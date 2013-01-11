<?php

namespace Samson\Bundle\FilterBundle\Filter;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\ORM\QueryBuilder;
use ReflectionObject;
use Samson\Bundle\CoreBundle\DataSerializer\DataSerializer;
use Samson\Bundle\FilterBundle\Entity\FilterPreset;
use Samson\Bundle\FilterBundle\Entity\FilterValues;
use Samson\Bundle\FilterBundle\Form\ChoiceList\FilterPresetChoiceList;
use Samson\Bundle\SecurityBundle\Entity\AbstractUser;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Exception\InvalidPropertyException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\Util\PropertyPath;

class Filter
{
    private $reader;

    private $doctrine;

    private $securityContext;

    private $session;

    private $router;

    private $container;

    private $serializer;

    public function __construct(
    FileCacheReader $reader, Registry $doctrine, SecurityContext $sc, Session $session, Router $router, Container $container, DataSerializer $serializer, array $config
    )
    {
        $this->reader = $reader;
        $this->doctrine = $doctrine;
        $this->securityContext = $sc;
        $this->session = $session;
        $this->router = $router;
        $this->container = $container;
        $this->serializer = $serializer;
        $this->config = $config;
    }

    /**
     * 
     * @param Request $request
     * @param Form $filterForm
     * @param mixed $qb QueryBuilder or array
     */
    public function bindAndFilter(Request $request, Form $filterForm, $qb)
    {
        if ($request->request->has($filterForm->getName())) {
            $filterForm->bind($request->request->get($filterForm->getName()));
        }
        if($qb instanceof QueryBuilder ){
            $qb = array($qb);
        }
        
        $data = $filterForm->getData();

        if (array_key_exists('remember', $data) && $data['remember']) {
            if ($request->getMethod() != 'POST') {
                if (!$request->query->has('page')) {
                    if ($this->session->has('session_page['.$filterForm->get('data')->getName().']')) {
                        $request->query->set('page', $this->session->get('session_page['.$filterForm->get('data')->getName().']'));
                    }
                } else {
                    $this->session->set('session_page['.$filterForm->get('data')->getName().']', $request->query->get('page'));
                }
            } else {
                $this->session->set('session_page['.$filterForm->get('data')->getName().']', 1);
            }
        }

        foreach($qb as $currentQb){
            $this->filter($filterForm->getData(), $filterForm->get('data')->getData(), $currentQb);
        }
    }

    public function filter($data, $filterData, QueryBuilder $qb)
    {
        if (null === $data || null === $filterData) {
            return $qb;
        }

        $reflectionObject = new ReflectionObject($filterData);

        $parents = array();

        $parent = $reflectionObject;
        while (false !== ($parent = $parent->getParentClass())) {
            $parents[] = $parent;
        }

        $aliases = $qb->getRootAliases();

        $leftJoins = array();

        $parameterNameGenerator = new ParameterNameGenerator();

        $properties = array();
        $parents[] = $reflectionObject;
        foreach ($parents as $refObject) {
            $properties = array_merge($properties, $refObject->getProperties());
        }

        foreach ($properties as $property) {
            $propertyPath = new PropertyPath($property->getName());
            $value = $propertyPath->getValue($filterData);

            foreach ($this->reader->getPropertyAnnotations($property) as $annotation) {
                if ($annotation instanceof FieldSearch) {

                    $fieldFilterClass = $annotation->filteredBy();
                    $fieldFilter = new $fieldFilterClass($parameterNameGenerator, $qb);

                    $propertyPaths = $annotation->propertyPaths;
                    if (empty($propertyPaths)) {
                        $propertyPaths = array($property->getName());
                    }

                    $exprs = array();
                    $havingExprs = array();
                    foreach ($propertyPaths as $propertyPath) {
                        $having = false;
                        if (strpos($propertyPath, '~') === 0) {
                            $alias = null;
                            $propertyPath = substr($propertyPath, 1);
                            $having = true;
                        } elseif (strpos($propertyPath, ".") !== false) {
                            list($alias, $propertyPath) = $this->getLeftJoinAlias($aliases[0], $propertyPath, $aliases, $qb);
                        } else {
                            $alias = $aliases[0];
                        }

                        if (null !== $alias) {
                            $propertyPath = $alias.'.'.$propertyPath;
                        }

                        list ($expr, $parameters) = $fieldFilter->filter($propertyPath, $value, $annotation);
                        if (null === $expr)
                            continue;

                        foreach($parameters as $key => $value) {
                            $qb->setParameter($key, $value);
                        }
                        
                        if ($having) {
                            $havingExprs[] = $expr;
                        } else {
                            $exprs[] = $expr;
                        }
                    }

                    if (count($exprs)) {
                        $qb->andWhere("(".call_user_func_array(array($qb->expr(), 'orX'), $exprs).")");
                    }
                    if (count($havingExprs)) {
                        $qb->andHaving("(".call_user_func_array(array($qb->expr(), 'orX'), $havingExprs).")");
                    }
                }
            }
        }

        return $qb;
    }

    private function getLeftJoinAlias($parentAlias, $propertyPath, &$aliases, QueryBuilder $qb)
    {
        list($relation, $propertyPath) = explode('.', $propertyPath, 2);

        $alias = 'a'.rand(0, 100000);

        if (!in_array($alias, $aliases)) {
            $qb->leftJoin($parentAlias.'.'.$relation, $alias);
            $aliases[] = $alias;
        }

        if (strpos($propertyPath, ".") !== false) {
            list($alias, $propertyPath) = $this->getLeftJoinAlias($alias, $propertyPath, $aliases, $qb);
        }
        return array($alias, $propertyPath);
    }

    public function saveFilterValues(array $data, $filterType)
    {
        $em = $this->doctrine->getEntityManager();
        $user = $this->securityContext->getToken()->getUser();

        $entity = $this->getFilterValuesForCurrentUser($filterType);

        $remember = $this->config['use_remember'] ? $data['remember'] : true;
        $entity->setRemember($remember);
        $entity->setData($this->serialize($remember ? $data['data'] : null));

        if (!$em->contains($entity)) {
            $em->persist($entity);
        }

        $em->flush();

        return $entity;
    }

    public function getFilterValues($user, $filterType)
    {
        $em = $this->doctrine->getEntityManager();
        $er = $em->getRepository('SamsonFilterBundle:FilterValues');

        $values = $er->findOneBy(array('user' => $user->getId(), 'filterType' => get_class($filterType)));
        if (null === $values) {

            $values = new FilterValues();
            $values->setUser($user);
            $values->setFilterType(get_class($filterType));
        }
        return $values;
    }

    public function getFilterValuesForCurrentUser($filterType)
    {
        $user = $this->securityContext->getToken()->getUser();
        return $this->getFilterValues($user, $filterType);
    }

    public function savePreset($filterType, FilterValues $data)
    {

        $filterPreset = new FilterPreset();
        $filterPreset->setFilterType(get_class($filterType));
        $filterPreset->setData($data->getData());

        $this->session->set('filter_preset', $filterPreset);

        $response = new RedirectResponse($this->router->generate('filter_savePreset', array('return_path' => $this->container->get('request')->getRequestUri())));
        $response->send();
        die();
    }

    public function loadPreset($filterType, $presetName)
    {
        if ($presetName == '_reset_') {
            $opt = $filterType->getDefaultOptions(array());
            if (!@$opt['data_class']) {
                $dataClass = str_replace('Filter\Type', 'Filter\Data', get_class($filterType));
                $dataClass = str_replace('FilterType', 'FilterData', $dataClass);
            } else {
                $dataClass = $opt['data_class'];
            }
            if (isset($opt['reset'])) {
                $preset = $opt['reset'];
            } else {
                $preset = new $dataClass;
            }
            $preset = $this->serialize($preset);
        } else {
            $presets = $this->getPresetsForUser($filterType, $this->securityContext->getToken()->getUser());
            if (array_key_exists($presetName, $presets)) {
                $preset = $presets[$presetName]->getData();
            }
        }
        return $preset;
    }

    public function getPresetsForUser($filterType, AbstractUser $user)
    {
        $results = array();

        $er = $this->doctrine->getEntityManager()->getRepository('SamsonFilterBundle:FilterPreset');
        $qb = $er->createQueryBuilder('p');
        $qb->where($qb->expr()->andx(
                $qb->expr()->eq('p.filterType', '?1'), $qb->expr()->orx(
                    $qb->expr()->eq('p.public', '?2'), $qb->expr()->eq('p.user', '?3')
                )
            ));
        $qb->setParameters(array(1 => get_class($filterType), 2 => true, 3 => $user->getId()));
        foreach ($qb->getQuery()->getResult() as $result) {
            $results[$result->getId()] = $result;
        }
        $typeOptions = $filterType->getDefaultOptions(array());
        if (isset($typeOptions['presets'])) {
            foreach ($typeOptions['presets'] as $name => $preset) {
                $filterPreset = new FilterPreset();
                $filterPreset->setFixed(true);
                $filterPreset->setName($name);
                $filterPreset->setData($this->serialize($preset));
                $results[$name] = $filterPreset;
            }
        }

        return $results;
    }

    public function getPresetChoiceList($filterType)
    {
        $user = $this->securityContext->getToken()->getUser();
        return new FilterPresetChoiceList($this->getPresetsForUser($filterType, $user));
    }

    public function reattach($entity)
    {
        $em = $this->doctrine->getEntityManager();
        if (!$em->getUnitOfWork()->isInIdentityMap($entity)) {
            $entity = $em->merge($entity);
        }
        return $entity;
    }

    public function deserialize($val)
    {
        try {
            return $this->serializer->deserialize($val);
        } catch (InvalidPropertyException $e) {
            return null;
        }
    }

    public function serialize($val)
    {
        return $this->serializer->serialize($val);
    }
}
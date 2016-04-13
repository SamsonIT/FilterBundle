Implement a filter in a Symfony Controller
============


Step1: your FilterData-object
=
Filterparams are defined in a FilterData-object, it's a POPO:

```php
namespace Foo\Bar\Form\Filter;

use Samson\FilterBundle\Filter\Search as Filter;

class CompanyFilter {
  /**
   * @Filter\IntegerFieldSearch(propertyPath="id")
   */
  private $id;
  
  /**
   * @Filter\StringFieldSearch()
   */
  private $name;
  
  public function getId() {
    return $this->id;
  }
  
  public function setId($id) {
    $this->id = $id;
  }
  
  public function getName() {
    return $this->name;
  }
  
  public function setName($name) {
    $this->name = $name;
  }
}
```
**Please note the use-statement**


Options (all)
=
  * propertyPath (the path to the filtered property, e.g. "person.lastName". It's optional, by default it takes the name of the variable)
  * propertyPaths(same as propertyPath, but used for collections. E.g. "addresses.city" to find city-names in a collection of Addresses)

StringFieldSearch
=
Options (default: type):
  * type ('contains', 'begins_with', 'ends_with', 'equals')

IntegerFieldSearch
=
Options (default: type):
  * type ('equals'/'=', 'is less than'/'<', 'is less than or equal to'/'<=', 'is greater than'/'>', 'is greater than or equal to'/'>=', 'is not equal to'/'<>')

Step 2: FilterType (the Form)
=
Define a Form to glue your FilterData-object to.

```php
namespace Foo\Bar\Form\Filter;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\AbstractType;

class CompanyFilterType extends AbstractType {
  public function buildForm(FormBuilder $builder, array $options) {
    $builder
      ->add('id', null, array('required'=>false))
      ->add('name', null, array('required'=>false))
    ;
  }

  public function getName() {
    return 'company_filter';
  }

  public function getDefaultOptions(array $options) {
    return array(
      'data_class' => 'Foo\Bar\Form\Filter\CompanyFilter'
    );
  }
}
```
**Please note the data_class option. It's necessary to add this, otherwise the values cannot be stored**


Step 3: Usage in your Controller
=
Create a Form like any other form:

```php
$filter = $this->createForm('filter', null, array(
  'filter_type' => new CompanyFilterType(),
  'filter_data' => new CompanyFilter()
));
```
Create the QueryBuilder you are going to use to fetch filtered entities:

```php
$qb = $em->getRepository('FooBundle:Company')->createQueryBuilder('b')->orderBy('b.id');
$this->get('samson.filter')->bindAndfilter($this->getRequest(), $filter, $qb);
```
Step 4: Create your view
=
Create the view of your form in a Twig-template. No need for a submit button.

```twig
<form action="{{ path('company') }}" method="post" {{ form_enctype(filter) }}>
{{ form_widget(filter) }}
</form>
```

Override the inner part of your Filter like this:
```twig
{% block company_filter_widget %}
    <table>
        {% for field in form %}
            {{ form_row(field) }}
        {% endfor %}
    </table>
{% endblock %}
```

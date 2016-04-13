UPGRADE
======

## 3.x to 4.0

### PHP7-compatible

Certain annotation-classes have to be renamed to be compatible with PHP7:
- Boolean
- Integer
- String

We prepended all Filter-annotations to *SearchField.


Before:

```
@Filter\Integer()
```

After:

```
@Filter\IntegerSearchField()
```

This is applicable for all Filter-annotations.

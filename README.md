# appenda-base-property

Note: Project was originally written in 2008/2009, saved in Google Code until the service was shut down, and then archived to Github.

## Description

This project deserves a long introduction. A property set is a collection of variables (or attributes), handled in a strict-type fashion.

### MAGIC METHODS

Assuming the following attribute set and registered attributes:

```php
$propertySet = new Appenda_Property_Set();
$propertySet->register ('Sample', 'String');
$propertySet->register ('Mapping', 'Array');
```

The following accessors and mutators are magically available:

```php
$propertySet->getSample();
$propertySet->setSample ('Some Value');
```

The following basic array methods are available as well for array types:

```
$propertySet->getMapping ('Key');
$propertySet->setMapping ('Key', 'Value');
$propertySet->appendToMapping ('Value');
$propertySet->appendToMapping ('Key', 'Value');
$propertySet->prependToMapping ('Value');
$propertySet->prependToMapping ('Key', 'Value');
$propertySet->countMapping();
```

Inspection of internal properties are available:

```php
$propertySet->inspectSample(); // Returns the internal Appenda_Property object.
```

The following value tests are available, and return true/false. While not shown, all registered property types have access to these methods.

```php
$propertySet->isSampleNull();
$propertySet->isSampleNotNull();
$propertySet->isSampleEmpty();
$propertySet->isSampleNotEmpty();
$propertySet->isSampleTrue(); // Strict type checking.
$propertySet->isSampleFalse(); // String type checking.
```

The following assertations are available, and throw an exception if `ASSERT_BAIL` is enabled, print a warning and return an Exception object if `ASSERT_WARNING` is enabled, or simply do nothing and return null is `ASSERT_ACTIVE` is off. While not shown, all registered property types have access to these methods.

```php
$propertySet->assertSampleNull();
$propertySet->assertSampleNotNull();
$propertySet->assertSampleEmpty();
$propertySet->assertSampleNotEmpty();
$propertySet->assertSampleTrue(); // Strict type checking.
$propertySet->assertSampleFalse(); // Strict type checking.
```

### PROPERTY MODES

Modes are used to control the behavior of individual attributes. The modes are:

- MODE_ACCESSOR
- MODE_ALIAS
- MODE_CALLBACK
- MODE_CALLBACK_CACHED
- MODE_COMPRESSED
- MODE_INTERNAL
- MODE_LOCKED
- MODE_MUTATOR
- MODE_PERMISSIVE
- MODE_SHARED

Modes are bitmapped values, so you can do a bitwise | operator to combine several modes in a single method call. To apply a mode to a property:

```
$propertySet = new Appenda_Property_Set();
$propertySet->register ('Sample', 'string');
$propertySet->registerMode (Appenda_Property_Set::MODE_COMPRESSED);
```

### TEMPLATING

This class has two main methods (`registerInstance()`, `newInstance()`) that allow you to save a cloned instance of a validated `Appenda_Property_Set`, so you can recreate the instance in the future without incuring the validation cost again.

For example, a complex object might include 10-15 attributes, each of which have 1-3 modes and perhaps 1-3 rules. Each time any of the `registerXX()` methods are called, an extensive set of name, type, and mode validations are performed. This is done to ensure that the internal state of the attribute set remains intact at all times.

By calling the `registerInstance()` method after the first instance of a class, a validated copy is saved, and subsequent instances can be created (fully initialized and validated) by using the newInstance() method.

This is a substantial performance savings, even for simple objects:

Using unit tests to benchmark, a simple attribute set with three attributes and no rules or modes shows an 80% decrease in creation time. A complex attribute set with approximately 10 attributes, 5 modes, and 10 rules show an improvement of two orders of magnitude. Registering and cloning template is quite simple...

### INHERITANCE

The easiest way to use a `Appenda_Property_Set` is to inherit from it. This gives you full access to the magic get, set, is, and assertIs methods, as well as providing a clean way to initialize the attributes, rules, and modes. For example:

```php
class WebsiteRequest extends Appenda_Property_Set {
  protected function __construct() {
    $this->register ('Host', 'string');
    $this->register ('RequestUrl', 'string');
    $this->registerTemplate (CLASS);
  }

  static public function newInstance (array $aRequestParams)
  {
    // Create or clone the template instance
    $oInstance = parent::newInstance (__CLASS__);

    // Update the internal attributes
    $oInstance->setHost ($aRequestParams['REQUEST_HOST']);
    $oInstance->setRequestUrl ($aRequestParams['REQUEST_URI'));

    // Development-time assertations
    $oInstance->assertHostNotEmpty();
    $oInstance->assertRequestUrlNotEmpty();

    // Return the validated instance
    return $oInstance;
  }
}
```

### FILTERS

Filters are a special set of conditions that are tested prior to the updating of a property's value. All filters must return true for the value to be updated. You can assign any number of filters, in any combination of the following, with multiple filters of the same type allowed:

- Appenda_Property_Filter::CALLBACK
- Appenda_Property_Filter::INVERSE_REGEX
- Appenda_Property_Filter::MAXIMUM
- Appenda_Property_Filter::MINIMUM
- Appenda_Property_Filter::REGEX
- Appenda_Property_Filter::UNSIGNED

Most notably, callback filters can be assigned, so you can pass the data through an external function someplace for validation.

Filters can be set directly using the `Appenda_Property_Set` class:

```php
$propertySet = new Appenda_Property_Set();

$propertySet->register ('RangedInteger', 'Integer');
$propertySet->appendFilter ('RangedInteger', Appenda_Property_Filter::MINIMUM, -10);
$propertySet->appendFilter ('RangedInteger', Appenda_Property_Filter::MAXIMUM, +10);

$propertySet->register ('RangedString', 'String');
$propertySet->appendFilter ('RangedString', Appenda_Property_Filter::MINIMUM, 4);
$propertySet->appendFilter ('RangedString', Appenda_Property_Filter::MAXIMUM, 8);

// A more complicated example:
// Note the multiple regex rules. In this case, BOTH regexes have to match, which
// effectively means that the password has to have both at least one number and one
// letter, and the password as a whole also has to have a minimum length of 8
// characters and a maximum of 100.

$propertySet->register ('PasswordString', 'String');
$propertySet->appendFilter ('PasswordString', Appenda_Property_Filter::MINIMUM, 8);
$propertySet->appendFilter ('PasswordString', Appenda_Property_Filter::MAXIMUM, 100);
$propertySet->appendFilter ('PasswordString', Appenda_Property_Filter::REGEX, '/[a-zA-Z]+/');
$propertySet->appendFilter ('PasswordString', Appenda_Property_Filter::REGEX, '/[0-9]+/');
```

Filters can also be set and accessed on the individual Appenda_Property itself, using the inspectPropertyName() and then accessing the 'Filters' field:

```php
$oProperty = $propertySet->register ('PasswordString', 'String');
'$oProperty->appendToFilters (...);
$oProperty->getFilters();
$oProperty->setFilters (...);
```

### TRIGGERS

Triggers are similar to filters, but are exclusively callbacks that are executed in order after the field is updated. No return value or success/failure is checked.

Triggers are added in a similar fashion to filters:

```php
$propertySet = new Appenda_Property_Set();

$propertySet->register ('OrderStatus', 'Integer');
$propertySet->appendTrigger ('OrderStatus', array ('AuditHandler', 'logOrderChange'));
$propertySet->appendTrigger ('OrderStatus', array ('OrderHandler', 'updateStatus'));
```

http://code.google.com/p/ianzepp/source/browse/trunk/appenda-property SVN Source Tree


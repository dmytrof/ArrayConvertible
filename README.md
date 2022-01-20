# ArrayConvertible
====================

This library helps you to convert object to array

## Installation

    $ composer require dmytrof/array-convertible 

## Usage

Supported properties types: 
    `array`, 
    `bool`, 
    `float`, 
    `int`, 
    `string`, 
    `null`, 
    `ArrayConvertibleInterface`

### Example 1:
    <?php

    use Dmytrof\ArrayConvertible\ToArrayConvertibleInterface;
    use Dmytrof\ArrayConvertible\Traits\ToArrayConvertibleTrait; 
        
    class SomeClass implements ToArrayConvertibleInterface
    {
        use ToArrayConvertibleTrait;

        public $foo = 1;
        protected $bar = 'bar';
        private $baz = [
            'hello' => 'world',
            4,
            null,
        ];
    }

    $obj = new SomeClass();
    print_r($obj->toArray());

#### Result:
    [
        'foo' => 1,
        'bar' => 'bar',
        'baz' => [
            'hello' => 'world',
            4,
            null,
        ],
    ]

### Example 2 (Exclude no needed properties):

To exclude some properties from toArray result add const `TO_ARRAY_NOT_CONVERTIBLE_PROPERTIES` or `ARRAY_NOT_CONVERTIBLE_PROPERTIES` to your class or 
redefine `getToArrayNotConvertibleProperties` method in your class.

    <?php

    use Dmytrof\ArrayConvertible\ToArrayConvertibleInterface;
    use Dmytrof\ArrayConvertible\Traits\ToArrayConvertibleTrait; 
        
    class SomeClass implements ToArrayConvertibleInterface
    {
        use ToArrayConvertibleTrait;

        private const ARRAY_NOT_CONVERTIBLE_PROPERTIES = ['notConvertibleProperty'];

        public $foo = 1;
        protected $bar = 'bar';
        private $baz = [
            'hello' => 'world',
            4,
            null,
        ];
        private $notConvertibleProperty; // Must be avoided in toArray
    }

    $obj = new SomeClass();
    print_r($obj->toArray());

#### Result:
    [
        'foo' => 1,
        'bar' => 'bar',
        'baz' => [
            'hello' => 'world',
            4,
            null,
        ],
    ]

### Example 3 (Extend to convert properties of unsupported types):

To convert properties of unsupported types extend method `convertToArrayValue`.

    <?php

    use Dmytrof\ArrayConvertible\ToArrayConvertibleInterface;
    use Dmytrof\ArrayConvertible\Traits\ToArrayConvertibleTrait; 
        
    class SomeClass implements ToArrayConvertibleInterface
    {
        use ToArrayConvertibleTrait {
            convertToArrayValue as private __convertToArrayValue;
        }

        private const TO_ARRAY_NOT_CONVERTIBLE_PROPERTIES = ['notConvertibleProperty'];

        public $foo = 1;
        protected $bar = 'bar';
        private $baz = [
            'hello' => 'world',
            4,
            null,
        ];
        private $closure;
        private $notConvertibleProperty; // Must be avoided in toArray

        public function __construct()
        {
            $this->closure = function() {
                return 55;
            };
        }

        public function convertToArrayValue($value, string $property = null)
        {
            try {
                return $this->__convertToArrayValue($value, $property);
            } catch (ToArrayConvertibleException $e) {
                if ($value instanceof \Closure) {
                    return $value->call($this);
                }
                throw $e;
            }
        }
    };

    $obj = new SomeClass();
    print_r($obj->toArray());

#### Result:
    [
        'foo' => 1,
        'bar' => 'bar',
        'baz' => [
            'hello' => 'world',
            4,
            null,
        ],
        'closure' => 55,
    ]
    
        
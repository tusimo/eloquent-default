<?php

namespace Tusimo\EloquentDefault;

use Illuminate\Support\Str;

trait DefaultTrait
{

    protected $defaults = [

    ];

    /**
     * when columnStrict is true
     * we will only save the column defined in default to database
     * @var bool
     */
    protected $columnStrict = false;

    /**
     * default attributes for each model class
     * @var array
     */
    protected static $mutatorDefaultCache = [];

    public static function bootDefaultTrait()
    {
        static::saving(function ($model) {
            if ($defaults = $model->getDefaultAttributes()) {
                foreach ($defaults as $attribute) {
                    if (!$model->hasAttribute($attribute)) {
                        $model->$attribute = $model->getDefault($attribute);
                    }
                }
            }
            if ($model->columnStrict) {
                $columns = $model->getDefaultColumns();
                foreach ($model->attributes as $attribute => $value) {
                    if (!in_array($attribute, $columns)) {
                        if ($model->columnStrict) {
                            unset($model->attributes[$attribute]);
                        }
                    }
                }
            }
        });
    }

    /**
     * detect if attribute has set a default value
     * @param $attribute
     * @return bool
     */
    public function hasDefault($attribute)
    {
        return array_key_exists($attribute, $this->defaults) ||
            method_exists($this, $this->getDefaultMethod($attribute));
    }

    /**
     * detect if attribute has set
     * @param $attribute
     * @return bool
     */
    public function hasAttribute($attribute)
    {
        return array_key_exists($attribute, $this->attributes);
    }

    /**
     * detect jf attribute is default value
     * @param $attribute
     * @return bool
     */
    public function isDefault($attribute)
    {
        return !$this->hasAttribute($attribute) && $this->hasDefault($attribute);
    }

    /**
     * get the default value
     * @param $attribute
     * @return null
     */
    public function getDefault($attribute)
    {
        if (method_exists($this, $method = $this->getDefaultMethod($attribute))) {
            return $this->$method($attribute);
        } elseif ($this->hasDefault($attribute)) {
            return $this->defaults[$attribute];
        }
        return null;
    }

    /**
     * get the default method string
     * @param $attribute
     * @return string
     */
    private function getDefaultMethod($attribute)
    {
        return camel_case('get_' . $attribute . '_default');
    }


    protected function getDefaultColumns()
    {
        $columns = [];
        foreach ($this->defaults as $attribute => $default) {
            $columns[] = is_integer($attribute) ? $default : $attribute;
        }
        return collect($columns)->merge($this->getMutatedDefaultAttributes())->unique()->all();
    }

    protected function getDefaultAttributes()
    {
        $defaults = [];
        foreach ($this->defaults as $attribute => $default) {
            if (!is_integer($attribute)) {
                $defaults[] = $attribute;
            }
        }
        return collect($defaults)->merge($this->getMutatedDefaultAttributes())->unique()->all();
    }


    /**
     * Get the mutated default attributes for a given instance.
     *
     * @return array
     */
    public function getMutatedDefaultAttributes()
    {
        $class = static::class;

        if (! isset(static::$mutatorDefaultCache[$class])) {
            static::cacheMutatedDefaultAttributes($class);
        }

        return static::$mutatorDefaultCache[$class];
    }

    /**
     * Extract and cache all the default mutated attributes of a class.
     *
     * @param  string  $class
     * @return void
     */
    public static function cacheMutatedDefaultAttributes($class)
    {
        static::$mutatorDefaultCache[$class] = collect(static::getMutatorDefaultMethods($class))->map(function ($match) {
            return lcfirst(static::$snakeAttributes ? Str::snake($match) : $match);
        })->all();
    }

    /**
     * Get all of the default attribute mutator methods.
     *
     * @param  mixed  $class
     * @return array
     */
    protected static function getMutatorDefaultMethods($class)
    {
        preg_match_all('/(?<=^|;)get([^;]+?)Default(;|$)/', implode(';', get_class_methods($class)), $matches);

        return $matches[1];
    }
}

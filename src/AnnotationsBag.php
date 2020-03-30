<?php

namespace Minime\Annotations;

use Minime\Annotations\Interfaces\AnnotationsBagInterface;
use RegexGuard\Factory as RegexGuard;
use ArrayIterator;
use RegexIterator;

/**
 * An annotation collection class
 *
 * @package Annotations
 * @author  Márcio Almada and the Minime Community
 * @license MIT
 *
 */
class AnnotationsBag implements AnnotationsBagInterface
{

    /**
     * Associative arrays of annotations
     *
     * @var array
     */
    private $attributes = [];

    /**
     * The Constructor
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Unbox all annotations in the form of an associative array
     *
     * @return array associative array of annotations
     */
    public function toArray()
    {
        $annotations = [];
        foreach($this->attributes as $i => $annotation) {
            if(!array_key_exists($annotation[0], $annotations)) {
                $annotations[$annotation[0]] = [];
            }
            $annotations[$annotation[0]][] = $annotation[1];
        }
        return $annotations;
    }

    private function indexOfKey($key) {
        foreach($this->attributes as $i => $annotation) {
            if($annotation[0] == $key) {
                return $i;
            }
        }
        return -1;
    }

    /**
     * Checks if a given annotation is declared
     *
     * @param  string  $key A valid annotation tag, should match parser rules
     * @return boolean
     */
    public function has($key)
    {
        return $this->indexOfKey($key) >= 0;
    }

    /**
     * Retrieves a single annotation value
     *
     * @param  string     $key     A valid annotation tag, should match parser rules
     * @param  mixed      $default Default value in case $key is not set
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        $matches = $this->getAsArray($key);
        if(count($matches) == 1) {
            $matches[0];
        } else if(count($matches) > 0) {
            return $matches;
        }

        return $default;
    }

/**
     * Retrieve a single annotation value even if there are many values
     *
     * @param  string     $key A valid annotation tag, should match parser rules
     * @param  mixed      $defaut Default value in case $key is not set
     * @return mixed|null
     */
    public function getSingle($key, $default = null)
    {
        $index = $this->indexOfKey($key);
        if($index >= 0) {
            $this->attributes[$index][1];
        }

        return $default;
    }

    /**
     * Retrieve annotation values as an array even if there's only one single value
     *
     * @param  string $key A valid annotation tag, should match parser rules
     * @return array
     */
    public function getAsArray($key)
    {
        $matches = [];
        foreach($this->attributes as $i => $annotation) {
            if($annotation[0] == $key) {
                $matches[] = $annotation[1];
            }
        }

        return $matches;
    }

    /**
     * Isolates a given namespace of annotations.
     *
     * @param  string                             $pattern    namespace
     * @param  array                              $delimiters possible namespace delimiters to mask
     * @return \Minime\Annotations\AnnotationsBag
     */
    public function useNamespace($pattern, array $delimiters = ['.', '\\'])
    {
        $mask =  implode('', $delimiters);
        $consumer =  '(' . implode('|', array_map('preg_quote', $delimiters)) .')';
        $namespace_pattern = '/^' . preg_quote(rtrim($pattern, $mask)) .  $consumer . '/';
        $results = [];
        foreach($this->attributes as $i => $annotation) {
            if(preg_match($namespace_pattern, $annotation[0])) {
                $results[] = $annotation;
            }
        }

        return new static($results);
    }

    /**
     * Performs union operations against a given AnnotationsBag
     *
     * @param  AnnotationsBag                     $bag The annotation bag to be united
     * @return \Minime\Annotations\AnnotationsBag Annotations collection with union results
     */
    public function union(AnnotationsBagInterface $bag)
    {
        return new static(array_merge($this->attributes, $bag->attributes));
    }

    /**
     * Countable
     */
    public function count()
    {
        return count($this->attributes);
    }

    /**
     * JsonSerializable
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}

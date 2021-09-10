<?php

namespace Leuchtturm\Utilities\Reflection;

class ReflectionProperty
{
    private string $name;
    private string $type;
    private bool $hasDefaultValue;
    private mixed $defaultValue;
    private bool $isArrayType = false;


    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return str_starts_with($this->type, "?");
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ReflectionProperty
     */
    public function setName(string $name): ReflectionProperty
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->isNullable() ? ltrim($this->type, "?") : $this->type;
    }

    /**
     * @param string $type
     * @return ReflectionProperty
     */
    public function setType(string $type): ReflectionProperty
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    /**
     * @param bool $hasDefaultValue
     * @return ReflectionProperty
     */
    public function setHasDefaultValue(bool $hasDefaultValue): ReflectionProperty
    {
        $this->hasDefaultValue = $hasDefaultValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * @param mixed $defaultValue
     * @return ReflectionProperty
     */
    public function setDefaultValue(mixed $defaultValue): ReflectionProperty
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * @return bool
     */
    public function isArrayType(): bool
    {
        return $this->isArrayType;
    }

    /**
     * @param bool $isArrayType
     * @return ReflectionProperty
     */
    public function setIsArrayType(bool $isArrayType): static
    {
        $this->isArrayType = $isArrayType;
        return $this;
    }
}
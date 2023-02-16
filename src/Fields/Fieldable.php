<?php

namespace the42coders\Workflows\Fields;

/**
 * Trait Fieldable.
 *
 * Access the Data Fields of an Element.
 */
trait Fieldable
{
    /**
     * Return the Field value. If Field is not existing it returns the Field name.
     */
    public function getFieldValue(string $field): string
    {
        if (empty($field)) {
            return '';
        }

        if (! isset($this->data_fields[$field])) {
            return '';
        }

        return $this->data_fields[$field]['value'] ?? '';
    }

    /**
     * Returns the Field Type. If Field is not existing it returns an empty String.
     */
    public function getFieldType(string $field): string
    {
        return $this->data_fields[$field]['type'] ?? '';
    }

    /**
     * Check if the Field is from the passed resourceType.
     */
    public function fieldIsResourceType(string $field, string $resourceType): bool
    {
        return $this->getFieldType($field) === $resourceType;
    }

    /**
     * Pass selected back if the resourceType is selected for this field. If not an empty String.
     */
    public function fieldIsSelected(string $field, string $resourceType): string
    {
        return $this->fieldIsResourceType($field, $resourceType) ? 'selected' : '';
    }

    /**
     * Loads Resource Intelligence from the corresponding DataResourceClass.
     * If non is set its taking the first defined one from Config.
     */
    public function loadResourceIntelligence(string $field): string
    {
        if (! isset($this->data_fields[$field])) {
            $resources = config('workflows.data_resources');
            $class = reset($resources);
        } else {
            $className = $this->getFieldType($field);
            $class = new $className();
        }

        return $class::loadResourceIntelligence($this, $this->getFieldValue($field), $field);
    }

    public function inputFields(): array
    {
        return [];
    }

    public function inputField($key)
    {
        return $this->inputFields()[$key] ?? null;
    }
}

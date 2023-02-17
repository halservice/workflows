<?php

namespace the42coders\Workflows\DataBuses;

use Illuminate\Database\Eloquent\Model;

class DataBus
{
    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param Model $model
     * @param array $fields
     * @return void
     */
    public function collectData(Model $model, array $fields): void
    {
        foreach ($fields as $name => $field) {
            //TODO: Quick fix to remove description but handle/filter this better in the future :(

            if ($name === 'description') {
                continue;
            }

            $field_value = $field['value'] ?? '';

            if ($name === 'file' && ! $field_value) {
                continue;
            }

            $className = $field['type'] ?? ValueResource::class;
            $resource = new $className();

            $this->data[$name] = $resource->getData($name, $field_value, $model, $this);
        }
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        $output = '';

        foreach ($this->data as $line) {
            $output .= $line.'\n';
        }

        return $output;
    }

    /**
     * @param string $key
     * @param string|null $default
     * @return mixed|null
     */
    public function get(string $key, string $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setOutput(string $key, string $value)
    {
        $this->data[$this->get($key, $key)] = $value;
    }

    /**
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setOutputArray(string $key, string $value)
    {
        $this->data[$this->get($key, $key)][] = $value;
    }
}

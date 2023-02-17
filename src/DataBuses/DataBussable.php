<?php

namespace the42coders\Workflows\DataBuses;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait DataBussable
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo('the42coders\Workflows\Workflow');
    }

    /**
     * @param array $passedFields
     * @return array
     */
    public function getParentDataBusKeys(array $passedFields = []): array
    {
        $newFields = $passedFields;

        if (! empty($this->parentable)) {
            //foreach($this->parentable::$fields as $key => $value){
            //    $newFields[$key] = $this->parentable->name.' - '.$value;
            //}
            foreach ($this->parentable::$output as $key => $value) {
                $newFields[$this->parentable->name.' - '.$key.' - '.$this->parentable->getFieldValue($value)] = $value;
            }

            $newFields = $this->parentable->getParentDataBusKeys($newFields);
        }

        return $newFields;
    }

    /**
     * @param string $value
     * @param string $default
     * @return mixed
     */
    public function getData(string $value, string $default = ''): mixed
    {
        return $this->dataBus->get($value, $default);
    }

    /**
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setDataArray(string $key, string $value): void
    {
        $this->dataBus->setOutputArray($key, $value);
    }

    /**
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setData(string $key, string $value): void
    {
        $this->dataBus->setOutput($key, $value);
    }
}

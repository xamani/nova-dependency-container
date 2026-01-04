<?php

namespace Xamani\DependencyContainer;

trait HasChildFields
{
    protected $childFieldsArr = [];

    /**
     * @param  [array] $childFields [meta fields]
     * @return void
     */
    protected function extractChildFields($childFields)
    {
        foreach ($childFields as $childField) {
            if ($childField instanceof DependencyContainer) {
                $this->extractChildFields($childField->meta['fields']);
            } else {
                if (array_search($childField->attribute, array_column($this->childFieldsArr, 'attribute')) === false) {
                    // @todo: we should not randomly apply rules to child-fields.
                    $childField = $this->applyRulesForChildFields($childField);
                    $this->childFieldsArr[] = $childField;
                }
            }
        }
    }

    /**
     * @param  [array] $childField
     * @return [array] $childField
     */
    protected function applyRulesForChildFields($childField)
    {
        if (isset($childField->rules)) {
            $childField->rules = $this->ensureSometimesRule($childField->rules);
        }
        if (isset($childField->creationRules)) {
            $childField->creationRules = $this->ensureSometimesRule($childField->creationRules);
        }
        if (isset($childField->updateRules)) {
            $childField->updateRules = $this->ensureSometimesRule($childField->updateRules);
        }

        return $childField;
    }

    protected function ensureSometimesRule(array|string $rules): array
    {
        $rulesArray = is_array($rules) ? $rules : [$rules];
        if (!in_array('sometimes', $rulesArray, true)) {
            array_unshift($rulesArray, 'sometimes');
        }

        return $rulesArray;
    }
}

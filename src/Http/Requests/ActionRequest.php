<?php

namespace Xamani\DependencyContainer\Http\Requests;

use Illuminate\Support\Facades\Validator;
use Xamani\DependencyContainer\DependencyContainer;
use Xamani\DependencyContainer\HasChildFields;
use Laravel\Nova\Http\Requests\ActionRequest as NovaActionRequest;

class ActionRequest extends NovaActionRequest
{

    use HasChildFields;

    /**
     * Handles child fields.
     *
     * @return void
     */
    public function validateFields()
    {
        $availableFields = [];

        $action = $this->action();
        $reflection = new \ReflectionMethod($action, 'fields');
        $fields = $reflection->getNumberOfParameters() > 0 ? $action->fields($this) : $action->fields();

        foreach ($fields as $field) {
            if ($field instanceof DependencyContainer) {
                // do not add any fields for validation if container is not satisfied
                if ($field->areDependenciesSatisfied($this)) {
                    $availableFields[] = $field;
                    $this->extractChildFields($field->meta['fields']);
                }
            } else {
                $availableFields[] = $field;
            }
        }

        if ($this->childFieldsArr) {
            $availableFields = array_merge($availableFields, $this->childFieldsArr);
        }

        $fields = collect($availableFields);

        Validator::make(
            $this->all(),
            $fields->mapWithKeys(function ($field) {
                return $field->getCreationRules($this);
            })->all(),
            [],
            $fields->reject(function ($field) {
                return empty($field->name);
            })->mapWithKeys(function ($field) {
                return [$field->attribute => $field->name];
            })->all()
        )->validate();
    }
}

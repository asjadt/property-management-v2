<?php

namespace App\Rules;

use App\Models\Property;
use Illuminate\Contracts\Validation\Rule;

class PropertyBelongsToUser implements Rule
{
    protected $userId;
    protected $allowNull;
    protected $checkOwnership;

    /**
     * Create a new rule instance.
     *
     * @param int|null $userId
     * @param bool $allowNull
     * @param bool $checkOwnership
     */
    public function __construct($userId = null, $allowNull = true, $checkOwnership = true)
    {
        $this->userId = $userId ?? auth()->id();
        $this->allowNull = $allowNull;
        $this->checkOwnership = $checkOwnership;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Allow null if configured
        if ($this->allowNull && empty($value)) {
            return true;
        }

        // Check if property exists
        $property = Property::find($value);

        if (!$property) {
            return false;
        }

        // If ownership check is disabled, just verify existence
        if (!$this->checkOwnership) {
            return true;
        }

        // Check if property belongs to the user
        return $property->created_by == $this->userId;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The selected property is invalid or does not belong to you.';
    }
}

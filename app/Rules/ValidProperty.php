<?php

namespace App\Rules;

use App\Models\Property;
use Illuminate\Contracts\Validation\Rule;

class ValidProperty implements Rule
{
    protected $userId;
    protected $allowNull;
    protected $checkOwnership;
    protected $checkStatus;
    protected $requiredStatus;

    /**
     * Create a new rule instance.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->userId = $options['user_id'] ?? auth()->id();
        $this->allowNull = $options['allow_null'] ?? true;
        $this->checkOwnership = $options['check_ownership'] ?? false;
        $this->checkStatus = $options['check_status'] ?? false;
        $this->requiredStatus = $options['required_status'] ?? 'active';
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

        // Build query
        $query = Property::where('id', $value);

        // Check ownership if required
        if ($this->checkOwnership) {
            $query->where('created_by', $this->userId);
        }

        // Check status if required
        if ($this->checkStatus) {
            $query->where('status', $this->requiredStatus);
        }

        return $query->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->checkOwnership) {
            return 'The selected property is invalid or does not belong to you.';
        }

        if ($this->checkStatus) {
            return 'The selected property must be ' . $this->requiredStatus . '.';
        }

        return 'The selected property is invalid.';
    }
}

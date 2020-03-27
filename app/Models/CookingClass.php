<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CookingClass extends Model
{
    protected $fillable = [
        'date',
        'no_of_chef',
        'cost_per_chef',
        'no_of_assistant',
        'cost_per_assistant',
        'fuel_cost',
        'ingredient_cost',
        'other_cost',
        'no_of_participant',
        'cost_per_participant'
    ];

    protected $appends = [
        'balance',
        'costs',
        'earnings'
    ];

    public function setDateAttribute($value) {
        $date = Carbon::parse($value)->format('Y-m-d');

        $this->attributes['date'] = $date;
    }

    public function getCostPerChefAttribute($value) {
        return number_format($value, 2);
    }

    public function getCostPerAssistantAttribute($value) {
        return number_format($value, 2);
    }

    public function getFuelCostAttribute($value) {
        return number_format($value, 2);
    }

    public function getIngredientCostAttribute($value) {
        return number_format($value, 2);
    }

    public function getOtherCostAttribute($value) {
        return number_format($value, 2);
    }

    public function getCostPerParticipantAttribute($value) {
        return number_format($value, 2);
    }

    public function getCostsAttribute() {
        $costs = ($this->cost_per_chef * $this->no_of_chef) + ($this->cost_per_assistant * $this->no_of_assistant) + $this->fuel_cost + $this->ingredient_cost + $this->other_cost;

        return number_format($costs, 2);
    }

    public function getEarningsAttribute() {
        $earnings = $this->no_of_participant * $this->cost_per_participant;

        return number_format($earnings, 2);
    }

    public function getBalanceAttribute() {
        $balance = $this->earnings - $this->costs;

        return number_format($balance, 2);
    }
}

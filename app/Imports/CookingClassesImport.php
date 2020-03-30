<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use App\Models\CookingClass;
use Carbon\Carbon;

class CookingClassesImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts
{
    use Importable;

    protected $rows = 0;

    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row)
    {
        $this->rows++;

        $exists = CookingClass::whereDate('date', Carbon::parse($row['date'])->format('Y-m-d'))->where('category', $row['category'])->first();

        if($exists) {
            $exists->no_of_chef = $row['number_of_chef'];
            $exists->cost_per_chef = $row['cost_per_chef'];
            $exists->no_of_assistant = $row['number_of_assistants'];
            $exists->cost_per_assistant = $row['cost_per_assistants'];
            $exists->fuel_cost = $row['fuel_cost'];
            $exists->ingredient_cost = $row['ingredients_cost'];
            $exists->other_cost = $row['other_cost'];
            $exists->no_of_participant = $row['number_of_participants'];
            $exists->cost_per_participant = $row['earning_per_participant'];
            $exists->save();
            
            $save = $exists;

            $exists->delete();

            return $save;
        }

        return new CookingClass([
            'date' => $row['date'],
            'category' => $row['category'],
            'date' => $row['date'],
            'category' => $row['category'],
            'no_of_chef' => $row['number_of_chef'],
            'cost_per_chef' => $row['cost_per_chef'],
            'no_of_assistant' => $row['number_of_assistants'],
            'cost_per_assistant' => $row['cost_per_assistants'],
            'fuel_cost' => $row['fuel_cost'],
            'ingredient_cost' => $row['ingredients_cost'],
            'other_cost' => $row['other_cost'],
            'no_of_participant' => $row['number_of_participants'],
            'cost_per_participant' => $row['earning_per_participant']
        ]);
    }

    public function rules(): array
    {
        return [
            '*.date' => 'required|date',
            '*.category' => 'required|in:am,pm',
            '*.number_of_chef' => 'required|numeric|min:0',
            '*.cost_per_chef' => 'required|numeric|min:0',
            '*.number_of_assistants' => 'nullable|numeric|min:0',
            '*.cost_per_assistants' => 'nullable|numeric|min:0',
            '*.fuel_cost' => 'nullable|numeric|min:0',
            '*.ingredients_cost' => 'nullable|numeric|min:0',
            '*.other_cost' => 'nullable|numeric|min:0',
            '*.number_of_participants' => 'required|numeric|min:0',
            '*.earning_per_participant' => 'required|numeric|min:0'
        ];
    }
    
    public function getRowCount(): int
    {
        return $this->rows;
    }
    
    public function batchSize(): int
    {
        return 1000;
    }
    
}

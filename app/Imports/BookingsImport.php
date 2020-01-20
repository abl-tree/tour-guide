<?php

namespace App\Imports;

use App\Models\TourBooking;
use App\Models\TourDeparture;
use App\Models\TourTitle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithValidation;

class BookingsImport implements ToCollection, WithChunkReading
{
    protected $option;

    protected $data;

    private $rows;

    public function __construct($option, $data) {
        $this->option = $option;

        $this->data = $data;
    }

    // public function model(array $row)
    // {
        // return new User([
        //     'name'     => $row[0],
        //     'email'    => $row[1],
        //     'password' => 'secret',
        // ]);
    // }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        if($this->option === 'airbnb' && count($rows) < 2) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'file' => ['File is empty or does not match to Airbnb CSV. Please upload the correct file.']
            ]);

            throw $error;
        } else if($this->option === 'fareharbor' && count($rows) < 9) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'file' => ['File is empty or does not match to Fareharbor CSV. Please upload the correct file.']
            ]);

            throw $error;
        }

        $tour = TourTitle::find($this->data['tour']);

        foreach ($rows as $key => $row) 
        {
            if($this->option === 'airbnb') {

                if($key === 0){
                    
                    $validator = Validator::make($rows[$key]->toArray(), [
                        '0' => [
                            'required',
                            function ($attribute, $value, $fail) {
                                if ($value !== 'Full Name') {
                                    $fail('First column does not match to Airbnb CSV file'.$value);
                                }
                            },
                        ],
                        '1' => [
                            'required',
                            function ($attribute, $value, $fail) {
                                if ($value !== 'Date booked') {
                                    $fail('Second column does not match to Airbnb CSV file');
                                }
                            },
                        ],
                        '2' => [
                            'required',
                            function ($attribute, $value, $fail) {
                                if ($value !== 'Party size') {
                                    $fail('Third column does not match to Airbnb CSV file');
                                }
                            },
                        ]
                    ]);

                    if ($validator->fails()) {
                        $error = \Illuminate\Validation\ValidationException::withMessages([
                            'file' => ['File is empty or does not match to Airbnb CSV. Please upload the correct file.']
                        ]);
            
                        throw $error;
                    }

                    continue;
                } 

                Validator::make($row->toArray(), [
                    '0' => 'required',
                    '2' => 'required'
                ])->validate();

                if(!is_numeric($row[2])) {
                    $isNAN = \Illuminate\Validation\ValidationException::withMessages([
                        'file' => ['Party size in row '.($key + 1).' is not a number.']
                    ]);
        
                    throw $isNAN;
                }

                $departure = $tour->departures()->whereDate('date', $this->data['date'])
                ->where('tour_id', $this->data['tour'])
                ->whereRaw('15 - (child_participants + adult_participants) >= '.$row[2])
                ->first();

                if(!$departure) {
                    $departure = $tour->departures()->create([
                        'date' => $this->data['date'],
                        'tour_id' => $this->data['tour'],
                        'info_id' => $tour->other_info->id
                    ]);
                }

                $departure->adult_participants = $departure->adult_participants + $row[2];

                $departure->save();

                try {

                    $tour_bookings = TourBooking::create([
                        'tour_departure_id' => $departure->id,
                        'name' => $row[0],
                        'party_size' => $row[2]
                    ]);
    
                    if($tour_bookings) $this->rows++;

                } catch (\Illuminate\Database\QueryException $th) {
                    throw $th;
                }

            } else if($this->option === 'fareharbor') {
                
                if($key <= 7) {

                    if($key === 7) {
                        $tmp = $rows[$key];
    
                        if($tmp[0] !== 'Contact' && $tmp[3] !== 'Pax') {
                            $error = \Illuminate\Validation\ValidationException::withMessages([
                                'file' => ['File is empty or does not match to Fareharbor CSV. Please upload the correct file.']
                            ]);
                
                            throw $error;
                        }
                    }

                    continue;
                }

                Validator::make($row->toArray(), [
                    '0' => 'required',
                    '1' => 'nullable',
                    '2' => 'nullable',
                    '3' => 'required'
                ])->validate();
                
                if(!is_numeric($row[3])) {
                    $isNAN = \Illuminate\Validation\ValidationException::withMessages([
                        'file' => ['Column Pax in row '.($key + 1).' is not a number.']
                    ]);
        
                    throw $isNAN;
                }

                $departure = $tour->departures()->whereDate('date', $this->data['date'])
                            ->where('tour_id', $this->data['tour'])
                            ->whereRaw('15 - (child_participants + adult_participants) >= '.$row[3])
                            ->first();

                if(!$departure) {
                    $departure = $tour->departures()->create([
                        'date' => $this->data['date'],
                        'tour_id' => $this->data['tour'],
                        'info_id' => $tour->other_info->id
                    ]);
                }

                $departure->adult_participants = $departure->adult_participants + $row[3];

                $departure->save();

                $tour_bookings = TourBooking::create([
                    'tour_departure_id' => $departure->id,
                    'name' => $row[0],
                    'party_size' => $row[3]
                ]);

                if($tour_bookings) $this->rows++;

            }
        }
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        if($this->option === 'airbnb') {
            return 2;
        } else if($this->option === 'fareharbor') {
            return 2;
        }
    }
    
    public function chunkSize(): int
    {
        return 15000;
    }
    
    // public function batchSize(): int
    // {
    //     return 10000;
    // }

    public function getRowCount(): int
    {
        return $this->rows;
    }
}

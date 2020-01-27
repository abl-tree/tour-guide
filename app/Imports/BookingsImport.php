<?php

namespace App\Imports;

use App\Models\PaymentType;
use App\Models\Schedule;
use App\Models\TourBooking;
use App\Models\TourDeparture;
use App\Models\TourTitle;
use App\Models\UserInfo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithValidation;
use DB;

class BookingsImport implements ToCollection, WithChunkReading
{
    protected $option, $data, $fhGuide;

    private $rows = 0;

    private $fhBooking = false;

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
        } else if($this->option === 'fareharbor' && count($rows) < 0) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'file' => ['File is empty or does not match to Fareharbor CSV. Please upload the correct file.']
            ]);

            throw $error;
        }

        $tour = TourTitle::find($this->data['tour']);

        DB::transaction(function() use ($rows, $tour) {
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

                    $tour_bookings = TourBooking::create([
                        'tour_departure_id' => $departure->id,
                        'name' => $row[0],
                        'party_size' => $row[2],
                        'source' => 'airbnb'
                    ]);
    
                    if($tour_bookings) $this->rows++;

                } else if($this->option === 'fareharbor') {
                    $tmp = $rows[$key];

                    if($tmp[0] === 'Crew name') {

                        $this->fhGuide = $rows[$key + 1][0];

                    }

                    if($tmp[0] === 'Contact' && $tmp[3] === 'Pax') {
                        $this->fhBooking = true;

                        continue;
                    } else {
                        if($this->fhBooking && $row[0] && $row[3]){

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
                            
                            $guide = $this->fhGuide;

                            $guideAssigned = UserInfo::whereRaw('CONCAT(first_name, " ", last_name) = "'.$guide.'"')->first();
            
                            $departure = $tour->departures()
                                        ->whereDate('date', $this->data['date'])
                                        ->where('tour_id', $this->data['tour'])
                                        ->when($guideAssigned, function($q) use ($guide) {
                                            $q->whereHas('schedule', function($q) use ($guide) {
                                                $q->whereHas('user', function($q) use ($guide) {
                                                    $q->whereHas('info', function($q) use ($guide) {
                                                        $q->whereRaw('CONCAT(first_name, " ", last_name) = "'.$guide.'"');
                                                    });
                                                });
                                            });
                                        })
                                        ->when(!$guideAssigned, function($q) {
                                            $q->whereDoesntHave('schedule');
                                        })
                                        ->whereRaw('15 - (child_participants + adult_participants) >= '.$row[3])
                                        ->first();

                            if(!$departure) {
                                $departure = $tour->departures()
                                        ->whereDate('date', $this->data['date'])
                                        ->where('tour_id', $this->data['tour'])
                                        ->whereDoesntHave('schedule')
                                        ->whereRaw('15 - (child_participants + adult_participants) >= '.$row[3])
                                        ->first();
                            }
            
                            if(!$departure) {
                                $schedule = null;

                                if($guideAssigned) {
                                    if($guideAssigned && $user = $guideAssigned->user()->first()) {
                                        $time = $tour->time;
                                
                                        if($time === 'am') $time = 'Morning';
                                        else if($time === 'pm') $time = 'Afternoon';
                                        else if($time === 'evening') $time = 'Evening';

                                        $schedule = Schedule::firstOrCreate(
                                                    [
                                                        'tour_title_id' => $tour->id, 
                                                        'user_id' => $user->id, 
                                                        'available_at' => $this->data['date'],
                                                        'shift' => $time
                                                    ],
                                                    [
                                                        'flag' => 1
                                                    ]
                                                );
                                    }

                                }

                                $payment_type_id = $tour && $tour->histories->first() && $tour->histories->first()->tour_rates->first() ? $tour->histories->first()->tour_rates->first()->id : null;
                                

                                if(!$payment_type_id) {
                                    $default_payment_type = PaymentType::first();

                                    $payment_type_id = $tour->histories()->first()->tour_rates()->where('payment_type_id', $default_payment_type->id)->first()->id;
                                }

                                $departure = $tour->departures()->create([
                                    'date' => $this->data['date'],
                                    'tour_id' => $this->data['tour'],
                                    'tour_rate_id' => $payment_type_id,
                                    'info_id' => $tour->other_info->id,
                                    'schedule_id' => $schedule ? $schedule->id : null
                                ]);
                            }
            
                            $departure->adult_participants = $departure->adult_participants + $row[3];
            
                            $departure->save();
            
                            $tour_bookings = TourBooking::create([
                                'tour_departure_id' => $departure->id,
                                'name' => $row[0],
                                'party_size' => $row[3],
                                'source' => 'fareharbor'
                            ]);
            
                            if($tour_bookings) {
                                $this->rows++;

                                $this->fhGuide = null;
                            }

                            continue;
                        }

                        $this->fhBooking = false;

                        continue;
                    }
                    
                    // if($key <= 7) {

                    //     if($key === 7) {    
                    //         if($tmp[0] !== 'Contact' && $tmp[3] !== 'Pax') {
                    //             $error = \Illuminate\Validation\ValidationException::withMessages([
                    //                 'file' => ['File is empty or does not match to Fareharbor CSV. Please upload the correct file.']
                    //             ]);
                    
                    //             throw $error;
                    //         }
                    //     }

                    //     continue;
                    // }

                }
            }          
        });

        if($this->option === 'fareharbor' && $this->rows === 0) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'file' => ['File is empty or does not match to Fareharbor CSV. Please upload the correct file.']
            ]);

            throw $error;
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

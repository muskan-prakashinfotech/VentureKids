<?php

namespace App\Exports;

use App\Models\Students;
use App\Models\StudentObservations;
use App\Models\StudentSkills;
use App\Models\StudentMindset;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class StudentObservationDataExport implements FromArray,WithHeadings
{
    protected $school_id;
    protected $student_id;
    protected $mindsetList;
    protected $param;

    public function __construct(array $param)
    {
        $this->param = $param;
        
        $this->school_id = $this->param['school_id'];
        
        if($this->param['source'] == 'Trainer') {
            $this->student_id = $this->param['student_id'];
        } 
        
    }

    public function headings():array{
        $heading[] = 'Student Name';
        $heading[] = 'Trainer Name';
        $heading[] = 'Level';
        $heading[] = 'Stream';
        $heading[] = 'Session';
        $heading[] = 'Session Image Uploaded';
        $heading[] = 'Add Anecdotal Record to Demonstrate Anything Remarkable';
        $heading[] = 'Skills';
        
        $this->mindsetList = StudentMindset::all()->pluck('mindset_name','id')->toArray();
        if(!empty($this->mindsetList)) {
            $heading = array_merge($heading, $this->mindsetList); 
        }

        return $heading;
    } 

    public function array(): array
    {
        $export_data = [];

        if($this->param['source'] == 'Trainer') { 
            $student_list = Students::select('id')->where('school_id', $this->school_id)->where('id', $this->student_id)->get();
        } else {
            $student_list = Students::select('id')->where('school_id', $this->school_id)->get();
        }
        
        if($student_list->count()) {

            $student_list = $student_list->toArray();
            
            $observation_data = StudentObservations::select(['id', 'student_id', 'trainer_id', 'grade_id', 'stream_id', 'session_id', 'session_image', 'remarkable_note', 'skill_id'])->with([
                'getStudent' => function($query) {
                    $query->select(['id', 'name']);
                },
                'getTrainer' => function($query) {
                    $query->select(['id', 'trainer_name']);
                },
                'getLevel' => function($query) {
                    $query->select(['id', 'grade']);
                },
                'getStream' => function($query) {
                    $query->select(['id', 'title']);
                },
                'getSession' => function($query) {
                    $query->select(['id', 'title']);
                },
                'studMindsetData'=> function($query) {
                    $query->select(['student_observation_id', 'mindset_id', 'value']);
                }
            ])->whereIn('student_id', $student_list)->get();

            if($observation_data->count()) {

                $skillList = StudentSkills::all()->pluck('skill_name','id')->toArray();

                $observation_data = $observation_data->toArray();

                foreach($observation_data as $key => $data) {
                    $export_data[$key][] = $data['get_student']['name'];
                    $export_data[$key][] = $data['get_trainer']['trainer_name'];
                    $export_data[$key][] = $data['get_level']['grade'];
                    $export_data[$key][] = $data['get_stream']['title'];
                    $export_data[$key][] = !empty($data['get_session'])? $data['get_session']['title'] : '';
                    $export_data[$key][] = !empty($data['session_image'])? 'Y' : 'N';
                    $export_data[$key][] = $data['remarkable_note'];

                    $skills = [];
                    if(!empty($skillList) && !empty($data['skill_id'])) {
                        $skillIDList = explode(",", $data['skill_id']);
                        foreach($skillIDList as $sId) {
                            $skills[] = $skillList[$sId];
                        }
                    }
                    $export_data[$key][] = implode(",", $skills);

                    if(!empty($this->mindsetList) && !empty($data['stud_mindset_data'])) { 
                        foreach($data['stud_mindset_data'] as $mVal) {
                            $export_data[$key][] = $mVal['value'];
                        }
                    }
                }   
            }
            
        }

        if(empty($export_data)) {
            $export_data[0][] = 'No data found';
        }

        return $export_data;
    }
    
}

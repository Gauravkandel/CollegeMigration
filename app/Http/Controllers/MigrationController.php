<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDO;

class MigrationController extends Controller
{
    public function academicYears()
    {
        // Fetch data from the UGC database
        DB::table('academic_years')->truncate();

        $data = DB::connection('ugc')->table('fiscalyear')->get();
        foreach ($data as $item) {

            $years = [
                'name' => $item->FSYEAR,
                'code' => $item->FSID,

            ];

            DB::connection('mysql')->table('academic_years')->insert($years);
        }

        return "Data migrated successfully.";
    }

    function subjects()
    {
        // DB::table('subjects')->truncate();


        $data = DB::connection('ugc')->table('tblsubject')->get();

        foreach ($data as $item) {
            DB::connection('mysql')->table('subjects')->insert([
                'name' => $item->SNAME,
                'code' => $item->PREFIX,
                'sid' => $item->SID
            ]);
        }


        // Changed Size of title into 100
        // Added  new columns nepali and descripion

        return "Data migrated successfully.";
    }

    function students()
    {
        DB::table('students')->truncate();

        DB::connection('mysql')->table('students')->truncate();

        $data = DB::connection('ugc')->table('tblstdmain')->get();

        foreach ($data as $item) {
            // Convert gender from tinyint to string
            $gender = ($item->GENDER == 1) ? 'Male' : 'Female';

            // Convert disability status
            $disabilityStatus = ($item->ISHANDI == 1) ? 'Yes' : 'No';
            $per_district = DB::connection('ugc')->table('district')->where('DISTRICT_CODE', $item->PERDIST)->first();
            $per_state = DB::connection('ugc')->table('stateinfo')->where('STATECODE', $item->PERSTATE)->first();
            $per_local = DB::connection('ugc')->table('locallevellist')->where('LEVELCODE', $item->PERVDC)->first();

            $temp_district = DB::connection('ugc')->table('district')->where('DISTRICT_CODE', $item->TEMPDIST)->first();
            $temp_state = DB::connection('ugc')->table('stateinfo')->where('STATECODE', $item->TEMPSTATE)->first();
            $temp_local = DB::connection('ugc')->table('locallevellist')->where('LEVELCODE', $item->TEMPVDC)->first();

            $egroup = DB::connection('ugc')->table('tblreligion')->where('SID', $item->SGROUP)->first();


            DB::connection('mysql')->table('students')->insert([
                'reg_no' => $item->REGNO,
                'first_name' => $item->FNAME,
                'last_name' => $item->LNAME,
                'nepali_name' => $item->NEPNAME,
                'mobile_number' => $item->STDCONTACT,
                'date_of_birth' => $item->DOBNDATE,
                'gender' => $gender,
                'caste_ethnicity' => $egroup?->SName ?? null,
                'disability_status' => $disabilityStatus,
                'citizenship_number' => $item->CITIZENSHIP,
                'permanent_district' => $per_district?->DISTRICT_NAME ?? null,
                'permanent_province' => strtoupper($per_state?->STATENAME ?? ''),
                'permanent_local_level' => $per_local?->LEVELNAME ?? null,
                'permanent_ward_no' => $item->PERWARD,
                'permanent_tole' => $item->PERTOLE,
                'permanent_house_no' => $item->PERLAND,
                'temporary_district' => $temp_district?->DISTRICT_NAME ?? null,
                'temporary_province' =>  strtoupper($temp_state?->STATENAME ?? ''),
                'temporary_local_level' => $temp_local?->LEVELNAME ?? null,
                'temporary_ward_no' => $item->TEMPWARD,
                'temporary_tole' => $item->TEMPTOLE,
                'temporary_house_no' => $item->TEMPLAND,
                'father_name' => $item->FATNAME,
                'father_contact' => $item->FATCONTACT,
                'father_email' => $item->FATEMAIL,
                'father_occupation' => $item->FATOCC,
                'mother_name' => $item->MOTNAME,
                'mother_contact' => $item->MOTCONTACT,
                'mother_email' => $item->MOTEMAIL,
                'mother_occupation' => $item->MOTOCC,
                'admission_year' => substr($item->REGNDATE, 0, 4), // Extract year from registration date
                'date_of_admission' => $item->REGNDATE,
                'is_graduated' => ($item->STDSTSTUS == 2) ? 1 : 0, // Assuming 2 means graduated
                'dropout_status' => $item->STDSTSTUS, // Assuming 3 means dropped out
                'dropout_date' => $item->REMNDATE,
                'dropout_reason' => $item->REMARKS,
                'created_at' => now(),
            ]);
        }

        return "Data migrated successfully.";
    }

    function positions()
    {
        // DB::table('positions')->truncate();

        $data = DB::connection('ugc')->table('tbldegination')->get();

        foreach ($data as $item) {
            DB::connection('mysql')->table('positions')->insert([
                'name' => $item->SNAME,
                'code' => $item->SID,
                'rank' => $item->RRANKING,
                'description' => $item->DDESC,
                'is_active' => 1,


            ]);
        }


        // Changed Size of title into 100
        // Added  new columns nepali and descripion

        return "Data migrated successfully.";
    }

    function category()
    {
        // DB::table('positions')->truncate();

        $data = DB::connection('ugc')->table('category')->get();

        foreach ($data as $item) {
            DB::connection('mysql')->table('job_categories')->insert([
                'name' => $item->CATNAME,
                'code' => $item->CATID,
                'is_active' => 1,






            ]);
        }


        // Changed Size of title into 100
        // Added  new columns nepali and descripion

        return "Data migrated successfully.";
    }

    function jobType()
    {
        // DB::table('positions')->truncate();

        $jobTypes = [
            ['code' => 'S001', 'name' => 'PERMANENT',    'is_active' => 1],
            ['code' => 'S002', 'name' => 'TEMPORARY',    'is_active' => 1],
            ['code' => 'S003', 'name' => 'CONTRACT',     'is_active' => 1],
            ['code' => 'S004', 'name' => 'PART TIME',    'is_active' => 1],
            ['code' => 'S005', 'name' => 'PERIOD BASIS', 'is_active' => 1],
        ];

        DB::connection('mysql')->table('job_types')->insert($jobTypes);

        // Changed Size of title into 100
        // Added new columns nepali and description

        return "Data migrated successfully.";
    }


    function staff()
    {
        DB::table('staff')->truncate();

        DB::connection('mysql')->table('staff')->truncate();
        $data = DB::connection('ugc')->table('employee_reg')->get();

        foreach ($data as $item) {
            $old_position = DB::connection('ugc')->table('tbldegination')->where('SID', $item->EMPDEGINATION)->first();
            $position = DB::connection('mysql')->table('positions')->where('code', $item->EMPDEGINATION)->first();
            $job_category = DB::connection('mysql')->table('job_categories')->where('code', $old_position->CATEGORYID)->first();
            $per_district = DB::connection('ugc')->table('district')->where('DISTRICT_CODE', $item->PER_DISTRICT)->first();
            $per_state = DB::connection('ugc')->table('stateinfo')->where('STATECODE', $item->PER_STATE)->first();
            $per_local = DB::connection('ugc')->table('locallevellist')->where('LEVELCODE', $item->PER_MPVDC)->first();

            $temp_district = DB::connection('ugc')->table('district')->where('DISTRICT_CODE', $item->PER_DISTRICT)->first();
            $temp_state = DB::connection('ugc')->table('stateinfo')->where('STATECODE', $item->PER_STATE)->first();
            $temp_local = DB::connection('ugc')->table('locallevellist')->where('LEVELCODE', $item->TEMP_MPVDC)->first();

            $egroup = DB::connection('ugc')->table('tblreligion')->where('SID', $item->EGROUP)->first();

            $job_type = DB::connection('mysql')->table('job_types')->where('code', $item->SVRTYPE)->first();








            // Convert gender from tinyint to string
            $gender = ($item->EMPGENDER == 1) ? 'Male' : 'Female';

            // Convert marital status

            DB::connection('mysql')->table('staff')->insert([
                'first_name' => $item->EMPFNAME,
                'middle_name' => null, // Not available in source
                'last_name' => $item->EMPLNAME,
                'nepali_name' => $item->EMPNEPNAME,
                'mobile_number' => $item->MOBILENO,
                'date_of_birth' => $item->DOBNDATE,
                'gender' => $gender,
                'caste_ethnicity' => $egroup?->SName ?? null,
                'disability_status' => 'Without Disability', // Not available in source
                'citizenship_number' => $item->CITIZENSHIPNO,
                'permanent_district' => $per_district?->DISTRICT_NAME ?? null,
                'permanent_province' => strtoupper($per_state?->STATENAME ?? ''),
                'permanent_local_level' => $per_local?->LEVELNAME ?? null,
                'permanent_ward_no' => ($item->PER_WARD && $item->PER_WARD !== 'N/A') ? $item->PER_WARD :  null,
                'permanent_tole' => $item->PER_TOLE,
                'permanent_house_no' => $item->PER_LAND,
                'current_district' => $temp_district?->DISTRICT_NAME ?? null,
                'current_province' => strtoupper($temp_state?->STATENAME ?? ''),
                'current_local_level' => $temp_local?->LEVELNAME ?? null,
                'current_ward_no' => ($item->TEMP_WARD && $item->TEMP_WARD !== 'N/A') ? $item->TEMP_WARD :  null,
                'current_tole' => $item->TEMP_TOLE,
                'current_house_no' => $item->TEMP_LAND,
                'spouse_name' => $item->SPONAME,
                'spouse_contact' => $item->SPOCON,
                'spouse_occupation' => $item->SPOOCC,
                'appointment_date' => $item->REGNDATE,
                'created_at' => now(),
                'position_id' => $position?->id ?? null, // To be filled later
                'job_type_id' => $job_type?->id ?? 1, // To be filled later
                'job_category_id' => $job_category?->id ?? null, // To be filled later
                'deleted_at' => null
            ]);
        }

        return "Employee data migrated successfully.";
    }


    function infrastructureTypes()
    {
        // DB::table('academic_years')->truncate();

        $data = DB::connection('ugc')->table('tbldepartment')->get();

        foreach ($data as $item) {
            DB::connection('mysql')->table('infrastructure_types')->insert([
                'name' => $item->DEPNAME,

            ]);
        }


        // Changed Size of title into 100
        // Added  new columns nepali and descripion

        return "Data migrated successfully.";
    }


    public function studentAcademicRecord()
    {
        DB::table('student_academic_records')->truncate();

        $data = DB::connection('ugc')->table('tblstdlevelinfo')->get();

        foreach ($data as $item) {
            // 1. Resolve student ID by REGNO
            $student = DB::connection('mysql')->table('students')->where('reg_no', $item->REGNO)->first();
            if (!$student) continue;

            // 2. Resolve faculty ID
            $faculty = DB::connection('mysql')->table('faculties')->where('code', 'LIKE', '%' . $item->FACULTY . '%')->first();

            // 3. Resolve level ID
            $level = DB::connection('mysql')->table('levels')->where('slug', 'LIKE', '%' . $item->SLEVEL . '%')->first();

            // $student_old = DB::connection('ugc')->table('tblstdmain')->where('REGNO', $item->REGNO)->first();

            $academic = DB::connection('mysql')->table('academic_years')->where('code', $item->ACAID)->first();

            // 4. Resolve program ID (you may need to map SCLASS)
            if (in_array($item->SCLASS, ["L006", "L018", "L022", "L048", "L028", "L053", "L055", "L024"])) {
                $program = DB::connection('mysql')->table('programs')->where('code', 'LIKE', '%' . $item->SCLASS . '%')->first();
            } else {
                $program = DB::connection('mysql')->table('programs')->where('code', $item->SCLASS)->first();
            }
            // 6. Dates - use Carbon to handle empty or invalid dates
            $startDate = Carbon::now()->toDateString(); // or get from another field if available
            $endDate = null;

            // 7. Insert into student_academic_records
            DB::connection('mysql')->table('student_academic_records')->insert([
                'student_id'        => $student->id,
                'faculty_id'        => $faculty ? $faculty->id : Null,
                'level_id'          => $level ? $level->id : Null,
                'academic_year_id'  => $academic ? $academic->id : Null,
                'program_id'        => $program ? $program->id : Null,
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'status'            => 'active',
                'notes'             => null,
                'is_current'        => 1,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        return "Student academic records migrated successfully.";
    }

    public function faculty()
    {
        // Fetch data from the UGC database
        $data = DB::connection('ugc')->table('tblleveldetail')->get();

        // Define the allowed SID values

        foreach ($data as $item) {
            if ($item->Type == 'FA' && $item->CMODE == 'RM02') {
                $faculty = [
                    'name' => $item->SName,
                    'code' => $item->SID,

                ];

                DB::connection('mysql')->table('faculties')->insert($faculty);
            }
        }

        return "Data migrated successfully.";
    }


    public function level()
    {
        // Fetch data from the UGC database
        $data = DB::connection('ugc')->table('tblleveldetail')->get();

        // Check if a "+2" record already exists in the levels table


        foreach ($data as $item) {
            if ($item->Type === 'LE' && $item->CMODE == 'RM02') {
                $title = $item->SName;


                $faculty = [
                    'title' => $title,
                    'slug' => $item->SID,
                ];

                DB::connection('mysql')->table('levels')->insert($faculty);
            }
        }


        return "Data migrated successfully.";
    }


    public function programs()
    {
        // Fetch data from the UGC database
        $data = DB::connection('ugc')->table('tblleveldetail')->get();
        foreach ($data as $item) {
            if ($item->Type === 'CL'  && $item->CMODE == 'RM02') {
                $levelId = $item->Parent;

                $level = DB::connection('mysql')->table('levels')->where('slug', 'LIKE', '%' . $levelId . '%')->first();
                if ($level) {
                    $tblevel = DB::connection('ugc')->table('tblleveldetail')->where('TYPE', 'LE')->where('SID', $levelId)->first();
                    if (!$tblevel) continue;

                    $facultyId = $tblevel->Parent;


                    $faculty = DB::connection('mysql')->table('faculties')->where('code', 'LIKE', '%' . $facultyId . '%')->first();
                } else {

                    $faculty = DB::connection('mysql')->table('faculties')->where('code', 'LIKE', '%' . $levelId . '%')->first();
                }


                if (!$faculty) continue;
                // 3. Resolve level ID
                $faculty = [
                    'faculty_id' => $faculty->id,
                    'level_id' => $level->id ?? null,
                    'name' => $item->SName,
                    'code' => $item->SID,

                ];

                DB::connection('mysql')->table('programs')->insert($faculty);
            }
        }

        return "Data migrated successfully.";
    }
    public function manageStudentAcademic()
    {
        // Program name to semester number mapping
        $programSemesterMap = [
            // ==== BBS ====
            "4yrs BBS 1st Year" => 1,
            "4yrs BBS 2nd Year" => 2,
            "4yrs BBS 3rd Year" => 3,
            "4yrs BBS 4th Year" => 4,

            // ==== B.Ed ====
            "4yrs B.Ed. 1st Year" => 1,
            "4yrs B.Ed. 2nd Year" => 2,
            "4yrs B.Ed. 3rd Year" => 3,
            "4yrs B.Ed. 4th Year" => 4,

            "3 Years B.Ed.1st Year" => 1,
            "3 Year B.Ed. 2nd Year" => 2,
            "3 Years B.Ed. 3rd Year" => 3,

            "1yr B.ED." => 1,

            // ==== B.A. ====
            "B.A.1ST Year" => 1,
            "B.A. 2nd Year" => 2,
            "B.A. 3rd Year" => 3,
            "B.A.4th Year" => 4,

            "4yrs B.A 1st Year" => 1,
            "4yrs B.A 2nd Year" => 2,
            "4yrs B.A 3rd Year" => 3,
            "4yrs B.A 4th Year" => 4,

            // ==== M.Ed ====
            "M.Ed 1st Semester" => 1,
            "M.Ed 2nd Semster"  => 2,
            "M.Ed 3rd Semster"  => 3,
            "M.Ed 4th Semster"  => 4,

            // ==== +2 ====
            "11 Eleven" => 1,
            "12 Twelve" => 1,
            "11 Eleven(Hum)" => 1,
            "12 Twelve(Hum)" => 1,
            "11 Eleven(Edu)" => 1,
            "12 Twelve(Edu)" => 1,
            "Eleven"    => 1,
            "Twelve"    => 1,

            // ==== School levels (all semester_number = 1) ====
            "Nurssary" => 1,
            "LKG"      => 1,
            "UKG"      => 1,
            "One"      => 1,
            "Two"      => 1,
            "Three"    => 1,
            "Four"     => 1,
            "Five"     => 1,
            "Six"      => 1,
            "Seven"    => 1,
            "Eight"    => 1,
            "Nine"     => 1,
            "Ten"      => 1,
        ];

        $students = DB::connection('mysql')->table('student_academic_records')->get();

        // Fetch first programs for reference
        $first_bbs = DB::connection('mysql')->table('programs')->where('name', "4yrs BBS 1st Year")->first();
        $first_bed = DB::connection('mysql')->table('programs')->where('name', "4yrs B.Ed. 1st Year")->first();
        $first_ba  = DB::connection('mysql')->table('programs')->where('name', "B.A.1ST Year")->first();
        $first_med = DB::connection('mysql')->table('programs')->where('name', "M.Ed 1st Semester")->first();
        $first_elv = DB::connection('mysql')->table('programs')->where('name', "11 Eleven")->first();
        $first_twe = DB::connection('mysql')->table('programs')->where('name', "12 Twelve")->first();
        $first_elvhum = DB::connection('mysql')->table('programs')->where('name', "11 Eleven(Hum)")->first();
        $first_twehum = DB::connection('mysql')->table('programs')->where('name', "12 Twelve(Hum)")->first();
        $first_elvedu = DB::connection('mysql')->table('programs')->where('name', "11 Eleven(Edu)")->first();
        $first_tweedu = DB::connection('mysql')->table('programs')->where('name', "12 Twelve(Edu)")->first();
        $first_ele = DB::connection('mysql')->table('programs')->where('name', "Eleven")->first();
        $first_tvl = DB::connection('mysql')->table('programs')->where('name', "Twelve")->first();
        $first_3bed = DB::connection('mysql')->table('programs')->where('name', "3 Years B.Ed.1st Year")->first();
        $first_1bed = DB::connection('mysql')->table('programs')->where('name', "1yr B.ED.")->first();
        $first_4ba = DB::connection('mysql')->table('programs')->where('name', "4yrs B.A 1st Year")->first();

        // Nursery – Ten (base programs)
        $first_nur = DB::connection('mysql')->table('programs')->where('name', "Nurssary")->first();
        $first_lkg = DB::connection('mysql')->table('programs')->where('name', "LKG")->first();
        $first_ukg = DB::connection('mysql')->table('programs')->where('name', "UKG")->first();
        $first_one = DB::connection('mysql')->table('programs')->where('name', "One")->first();
        $first_two = DB::connection('mysql')->table('programs')->where('name', "Two")->first();
        $first_three = DB::connection('mysql')->table('programs')->where('name', "Three")->first();
        $first_four  = DB::connection('mysql')->table('programs')->where('name', "Four")->first();
        $first_five  = DB::connection('mysql')->table('programs')->where('name', "Five")->first();
        $first_six   = DB::connection('mysql')->table('programs')->where('name', "Six")->first();
        $first_seven = DB::connection('mysql')->table('programs')->where('name', "Seven")->first();
        $first_eight = DB::connection('mysql')->table('programs')->where('name', "Eight")->first();
        $first_nine  = DB::connection('mysql')->table('programs')->where('name', "Nine")->first();
        $first_ten   = DB::connection('mysql')->table('programs')->where('name', "Ten")->first();

        foreach ($students as $student) {
            $program = DB::connection('mysql')->table('programs')->where('id', $student->program_id)->first();

            if ($program && isset($programSemesterMap[$program->name])) {
                $semesterNumber = $programSemesterMap[$program->name];

                // Determine which first program to use for semester reference based on exact program name
                switch ($program->name) {
                    // BBS Programs
                    case "4yrs BBS 1st Year":
                    case "4yrs BBS 2nd Year":
                    case "4yrs BBS 3rd Year":
                    case "4yrs BBS 4th Year":
                        $firstProgram = $first_bbs;
                        break;

                    // B.Ed Programs
                    case "4yrs B.Ed. 1st Year":
                    case "4yrs B.Ed. 2nd Year":
                    case "4yrs B.Ed. 3rd Year":
                    case "4yrs B.Ed. 4th Year":
                        $firstProgram = $first_bed;
                        break;

                    case "3 Years B.Ed.1st Year":
                    case "3 Year B.Ed. 2nd Year":
                    case "3 Years B.Ed. 3rd Year":
                        $firstProgram = $first_3bed;
                        break;

                    case "1yr B.ED.":
                        $firstProgram = $first_1bed;
                        break;

                    // B.A Programs
                    case "B.A.1ST Year":
                    case "B.A. 2nd Year":
                    case "B.A. 3rd Year":
                    case "B.A.4th Year":
                        $firstProgram = $first_ba;
                        break;

                    case "4yrs B.A 1st Year":
                    case "4yrs B.A 2nd Year":
                    case "4yrs B.A 3rd Year":
                    case "4yrs B.A 4th Year":
                        $firstProgram = $first_4ba;
                        break;

                    // M.Ed Programs
                    case "M.Ed 1st Semester":
                    case "M.Ed 2nd Semster":
                    case "M.Ed 3rd Semster":
                    case "M.Ed 4th Semster":
                        $firstProgram = $first_med;
                        break;

                    // +2 Programs
                    case "11 Eleven":
                        $firstProgram = $first_elv;
                        break;

                    case "12 Twelve":
                        $firstProgram = $first_twe;
                        break;
                    case "11 Eleven(Hum)":
                        $firstProgram = $first_elvhum;
                        break;

                    case "12 Twelve(Hum)":
                        $firstProgram = $first_twehum;
                        break;
                    case "11 Eleven(Edu)":
                        $firstProgram = $first_elvedu;
                        break;

                    case "12 Twelve(Edu)":
                        $firstProgram = $first_tweedu;
                        break;
                    case "Eleven":
                        $firstProgram = $first_ele;
                        break;

                    case "Twelve":
                        $firstProgram = $first_tvl;
                        break;

                    // School Programs
                    case "Nurssary":
                        $firstProgram = $first_nur;
                        break;

                    case "LKG":
                        $firstProgram = $first_lkg;
                        break;

                    case "UKG":
                        $firstProgram = $first_ukg;
                        break;

                    case "One":
                        $firstProgram = $first_one;
                        break;

                    case "Two":
                        $firstProgram = $first_two;
                        break;

                    case "Three":
                        $firstProgram = $first_three;
                        break;

                    case "Four":
                        $firstProgram = $first_four;
                        break;

                    case "Five":
                        $firstProgram = $first_five;
                        break;

                    case "Six":
                        $firstProgram = $first_six;
                        break;

                    case "Seven":
                        $firstProgram = $first_seven;
                        break;

                    case "Eight":
                        $firstProgram = $first_eight;
                        break;

                    case "Nine":
                        $firstProgram = $first_nine;
                        break;

                    case "Ten":
                        $firstProgram = $first_ten;
                        break;

                    default:
                        continue 2; // skip if no match (continue the foreach loop)
                }

                $semesterData = DB::connection('mysql')
                    ->table('program_semesters')
                    ->where('program_id', $firstProgram->id)
                    ->where('semester_number', $semesterNumber)
                    ->first();

                if ($semesterData) {
                    DB::connection('mysql')
                        ->table('student_academic_records')
                        ->where('id', $student->id)
                        ->update([
                            "program_id" => $semesterData->program_id,
                            'program_semester_id' => $semesterData->id
                        ]);
                }
            }
        }

        return "successful";
    }

    // public function manageStudentAcademic()
    // {
    //     // Program name to semester number mapping
    //     $programSemesterMap = [
    //         // BBS
    //         "BBS 1st Year"       => 1,
    //         "BBS 2nd Year"       => 2,
    //         "BBS 3rd Year"       => 3,
    //         "BBS 4th Year"       => 4,

    //         // MBS (Year/Semester/Matured)
    //         "MBS 1st Year"       => 1,
    //         "MBS 2nd Year"       => 2,
    //         "MBS 3RD SEM"        => 3,
    //         "MBS 4th SEM"        => 4,
    //         "MBS 1ST SEM"        => 1,
    //         "MBS 2ND SEM"        => 2,
    //         "MBS 5th SEM"        => 5,
    //         "MBS 6th SEM"        => 6,

    //         // BBA
    //         "BBA 1st Sem"        => 1,
    //         "BBA 2ND SEM"        => 2,
    //         "BBA 3rd SEM"        => 3,
    //         "BBA 4th SEM"        => 4,
    //         "BBA 5th SEM"        => 5,
    //         "BBA 6th SEM"        => 6,
    //         "BBA 7th SEM"        => 7,
    //         "BBA 8th SEM"        => 8,
    //     ];

    //     // Fetch all students
    //     $students = DB::connection('mysql')->table('student_academic_records')->get();

    //     // Pre-fetch first programs for mapping
    //     $first_bbs = DB::connection('mysql')
    //         ->table('programs')
    //         ->where('name', "BBS 1st Year")
    //         ->first();

    //     $first_mbs = DB::connection('mysql')
    //         ->table('programs')
    //         ->where('name', "MBS 1st Year")
    //         ->first();

    //     $first_bba = DB::connection('mysql')
    //         ->table('programs')
    //         ->where('name', "BBA 1st Sem")
    //         ->first();

    //     foreach ($students as $student) {
    //         $program = DB::connection('mysql')
    //             ->table('programs')
    //             ->where('id', $student->program_id)
    //             ->first();

    //         if ($program && isset($programSemesterMap[$program->name])) {
    //             $semesterNumber = $programSemesterMap[$program->name];

    //             // Decide which base program to use
    //             if (Str::contains($program->name, 'BBS')) {
    //                 $baseProgram = $first_bbs;
    //             } elseif (Str::contains($program->name, 'MBS')) {
    //                 $baseProgram = $first_mbs;
    //             } elseif (Str::contains($program->name, 'BBA')) {
    //                 $baseProgram = $first_bba;
    //             } else {
    //                 $baseProgram = null;
    //             }

    //             if ($baseProgram) {
    //                 $semesterData = DB::connection('mysql')
    //                     ->table('program_semesters')
    //                     ->where('program_id', $baseProgram->id)
    //                     ->where('semester_number', $semesterNumber)
    //                     ->first();

    //                 if ($semesterData) {
    //                     DB::connection('mysql')
    //                         ->table('student_academic_records')
    //                         ->where('id', $student->id)
    //                         ->update([
    //                             "program_id" => $semesterData->program_id,
    //                             'program_semester_id' => $semesterData->id
    //                         ]);
    //                 }
    //             }
    //         }
    //     }

    //     return "successful";
    // }
    function new_students()
    {
        DB::table('students')->truncate();

        DB::connection('mysql')->table('students')->truncate();

        $data = DB::connection('ugc')->table('tblstdmain')->get();

        foreach ($data as $item) {
            // Convert gender from tinyint to string
            $gender = ($item->GENDER == "Male") ? 'Male' : 'Female';

            // Convert disability status


            DB::connection('mysql')->table('students')->insert([
                'reg_no' => $item->REGNO,
                'first_name' => $item->STNAME,
                'last_name' => "",
                'nepali_name' => $item->NEPNAME,
                'date_of_birth' => $item->BIRTHNEP,
                'disability_status' => "without disability",
                'gender' => $gender,
                'caste_ethnicity' => $item->SGROUP,
                'created_at' => now(),
            ]);
        }

        return "Data migrated successfully.";
    }
    public function new_studentAcademicRecord()
    {
        // DB::table('student_academic_records')->truncate();

        // Fetch all records from the UGC database
        $data = DB::connection('ugc')
            ->table('tblstdmain')
            ->offset(500)    // skip first 500
            ->limit(5000)     // take next 500
            ->get();


        foreach ($data as $item) {

            $student = DB::connection('mysql')->table('students')->where('reg_no', $item->REGNO)->first();
            // 2. Resolve faculty ID
            $faculty = DB::connection('mysql')->table('faculties')->where('name', $item->SFACULTY)->first();
            if (!$faculty) continue;



            // 3. Resolve level ID
            $level = DB::connection('mysql')->table('levels')->where('title', $item->SLEVEL)->first();
            if (!$level) continue;



            $student_old = DB::connection('ugc')->table('tblstdmain')->where('REGNO', $item->REGNO)->first();


            $academic = DB::connection('mysql')->table('academic_years')->where('name', $item->BATCH)->first();
            if (!$academic) continue;

            // 4. Resolve program ID (you may need to map SCLASS)
            $program = DB::connection('mysql')->table('programs')->where('code', $item->SCLASS)->first();
            if (!$program) continue;


            // 6. Dates - use Carbon to handle empty or invalid dates
            $startDate = Carbon::now()->toDateString(); // or get from another field if available
            $endDate = null;

            // 7. Insert into student_academic_records
            DB::connection('mysql')->table('student_academic_records')->insert([
                'student_id'        => $student->id,
                'faculty_id'        => $faculty->id,
                'level_id'          => $level->id,
                'academic_year_id'  => $academic->id,
                'program_id'        => $program->id,
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'status'            => 'active',
                'notes'             => null,
                'is_current'        => 1,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        return "Student academic records migrated successfully.";
    }
    public function registerStudents()
    {
        DB::connection('mysql')->table('student_registrations')->truncate();
        $data  = DB::connection('ugc')->table('tblstdmain')->where('BOARDREGNO', '!=', '')->get();
        foreach ($data as $item) {
            $student = DB::connection('mysql')->table('students')->where('reg_no', $item->REGNO)->first();
            if (!$student) {
                continue;
            }

            $year = substr($item->BATCH, 0, 4);
            DB::connection('mysql')->table('student_registrations')
                ->updateOrInsert(
                    [
                        'student_id' => $student->id,
                        'registration_year' => $year,
                        'board_reg' => $item->BOARDREGNO

                    ],
                    [
                        'status' => 'registered',
                    ]
                );
        }
        return "student registered successfully";
    }
    public function externalExam()
    {
        try {
            // Get all unique exam_year and code combinations from source
            $sourceData = DB::connection('ugc')
                ->table('examformmain')
                ->select('YEARID as exam_year', 'SCLASS as code')
                ->distinct()
                ->get();

            if ($sourceData->isEmpty()) {
                return "No data found in source table";
            }

            // Get existing combinations from target table
            $existingExams = DB::connection('mysql')
                ->table('external_exams')
                ->select('exam_year', 'code')
                ->get()
                ->keyBy(function ($item) {
                    return $item->exam_year . '_' . $item->code;
                });

            $newRecords = [];

            foreach ($sourceData as $item) {
                $key = $item->exam_year . '_' . $item->code;

                if (!isset($existingExams[$key])) {
                    $newRecords[] = [
                        'exam_year' => $item->exam_year,
                        'code' => $item->code,
                    ];
                }
            }

            // Insert all new records in batch
            if (!empty($newRecords)) {
                DB::connection('mysql')
                    ->table('external_exams')
                    ->insert($newRecords);
            }

            return "Successful. Added " . count($newRecords) . " new records.";
        } catch (\Exception $e) {
            Log::error('External exam sync failed: ' . $e->getMessage());
            return "Failed: " . $e->getMessage();
        }
    }
    public function manageExternalExam()
    {
        $data = DB::connection('mysql')->table('external_exams')->get();
        foreach ($data as $item) {
            if (in_array($item->code, ["L018", "L070", "L098", "L068", "L028", "L090"])) {
                $program = DB::connection('mysql')->table('programs')->where('code', 'LIKE', '%' . $item->code . '%')->first();
            } else {
                $program = DB::connection('mysql')->table('programs')->where('code', $item->code)->first();
            }
            if ($program) {
                if (in_array($program->name, [
                    "4yrs BBS 1st Year",
                    "4yrs B.Ed. 1st Year",
                    "3 Years B.Ed.1st Year",
                    "1yr B.ED.",
                    "B.A.1ST Year",
                    "4yrs B.A 1st Year",
                    "M.Ed 1st Semester",
                    "11 Eleven",
                    "12 Twelve",
                    "11 Eleven(Hum)",
                    "12 Twelve(Hum)",
                    "11 Eleven(Edu)",
                    "12 Twelve(Edu)",
                    "Eleven",
                    "Twelve",
                    "Nurssary",
                    "LKG",
                    "UKG",
                    "One",
                    "Two",
                    "Three",
                    "Four",
                    "Five",
                    "Six",
                    "Seven",
                    "Eight",
                    "Nine",
                    "Ten"
                ])) {
                    $semesterNumber = 1;
                } else if (in_array($program->name, [
                    "4yrs BBS 2nd Year",
                    "4yrs B.Ed. 2nd Year",
                    "3 Year B.Ed. 2nd Year",
                    "B.A. 2nd Year",
                    "4yrs B.A 2nd Year",
                    "M.Ed 2nd Semster"
                ])) {
                    $semesterNumber = 2;
                } else if (in_array($program->name, [
                    "4yrs BBS 3rd Year",
                    "4yrs B.Ed. 3rd Year",
                    "3 Years B.Ed. 3rd Year",
                    "B.A. 3rd Year",
                    "4yrs B.A 3rd Year",
                    "M.Ed 3rd Semster"
                ])) {
                    $semesterNumber = 3;
                } else if (in_array($program->name, [
                    "4yrs BBS 4th Year",
                    "4yrs B.Ed. 4th Year",
                    "B.A.4th Year",
                    "4yrs B.A 4th Year",
                    "M.Ed 4th Semster"
                ])) {
                    $semesterNumber = 4;
                } else {
                    // Handle cases where program name doesn't match any known pattern
                    continue; // or set a default value
                }
                $program_new = DB::connection('mysql')->table('programs')->where('code', 'LIKE', '%' . $item->code . '%')->where('name', 'LIKE', '%' . '1' . '%')->first();
                if (!$program_new) {
                    continue;
                }
                $program_sem = DB::connection('mysql')->table('program_semesters')->where('program_id', $program_new->id)->where('semester_number', $semesterNumber)->first();
                if ($program_sem) {
                    DB::connection('mysql')->table('external_exams')->where('id', $item->id)->update(
                        [
                            'program_id' => $program_sem->program_id,
                            'semester_id' => $program_sem->id,
                        ]
                    );
                }
            }
        }
    }
    public function examForm()
    {
        // Get all exam forms at once
        $examForms = DB::connection('ugc')
            ->table('examformmain')
            ->orderBy('REGNO')
            ->get();

        foreach ($examForms as $item) {
            $student = DB::connection('mysql')
                ->table('students')
                ->where('reg_no', $item->REGNO)
                ->first();

            if (!$student) {
                continue;
            }
            $external_exam = DB::connection('mysql')
                ->table('external_exams')
                ->where('code', $item->SCLASS)
                ->where('exam_year', $item->YEARID)
                ->first();

            if (!$external_exam) {
                continue;
            }

            DB::connection('mysql')
                ->table('exam_forms')
                ->updateOrInsert(
                    [
                        'external_exam_id' => $external_exam->id,
                        'symbol_no'        => $item->SYMBOLNO,
                        'student_id'       => $student->id,
                        'status' => 'filled',
                    ],
                    []
                );
        }

        return "Successfully migrated Exam form";
    }
    public function programSubjects()
    {
        DB::table('program_semester_subjects')->truncate();
        // First get unique combinations
        $data = DB::connection('ugc')
            ->table('tblexamsubject')->get();

        $programSemesterMap = [
            // ==== BBS ====
            "4yrs BBS 1st Year" => 1,
            "4yrs BBS 2nd Year" => 2,
            "4yrs BBS 3rd Year" => 3,
            "4yrs BBS 4th Year" => 4,

            // ==== B.Ed ====
            "4yrs B.Ed. 1st Year" => 1,
            "4yrs B.Ed. 2nd Year" => 2,
            "4yrs B.Ed. 3rd Year" => 3,
            "4yrs B.Ed. 4th Year" => 4,

            "3 Years B.Ed.1st Year" => 1,
            "3 Year B.Ed. 2nd Year" => 2,
            "3 Years B.Ed. 3rd Year" => 3,

            "1yr B.ED." => 1,

            // ==== B.A. ====
            "B.A.1ST Year" => 1,
            "B.A. 2nd Year" => 2,
            "B.A. 3rd Year" => 3,
            "B.A.4th Year" => 4,

            "4yrs B.A 1st Year" => 1,
            "4yrs B.A 2nd Year" => 2,
            "4yrs B.A 3rd Year" => 3,
            "4yrs B.A 4th Year" => 4,

            // ==== M.Ed ====
            "M.Ed 1st Semester" => 1,
            "M.Ed 2nd Semster"  => 2,
            "M.Ed 3rd Semster"  => 3,
            "M.Ed 4th Semster"  => 4,

            // ==== +2 ====
            "11 Eleven" => 1,
            "12 Twelve" => 1,
            "11 Eleven(Hum)" => 1,
            "12 Twelve(Hum)" => 1,
            "11 Eleven(Edu)" => 1,
            "12 Twelve(Edu)" => 1,
            "Eleven"    => 1,
            "Twelve"    => 1,

            // ==== School levels (all semester_number = 1) ====
            "Nurssary" => 1,
            "LKG"      => 1,
            "UKG"      => 1,
            "One"      => 1,
            "Two"      => 1,
            "Three"    => 1,
            "Four"     => 1,
            "Five"     => 1,
            "Six"      => 1,
            "Seven"    => 1,
            "Eight"    => 1,
            "Nine"     => 1,
            "Ten"      => 1,
        ];
        foreach ($data as $item) {
            if (in_array($item->CID, ["L018", "L070", "L098", "L068", "L028", "L090"])) {
                $program = DB::connection('mysql')->table('programs')->where('code', 'LIKE', '%' . $item->CID . '%')->first();
            } else {
                $program = DB::connection('mysql')->table('programs')->where('code', $item->CID)->first();
            }
            if ($program) {
                if (in_array($program->name, [
                    "4yrs BBS 1st Year",
                    "4yrs B.Ed. 1st Year",
                    "3 Years B.Ed.1st Year",
                    "1yr B.ED.",
                    "B.A.1ST Year",
                    "4yrs B.A 1st Year",
                    "M.Ed 1st Semester",
                    "11 Eleven",
                    "12 Twelve",
                    "11 Eleven(Hum)",
                    "12 Twelve(Hum)",
                    "11 Eleven(Edu)",
                    "12 Twelve(Edu)",
                    "Eleven",
                    "Twelve",
                    "Nurssary",
                    "LKG",
                    "UKG",
                    "One",
                    "Two",
                    "Three",
                    "Four",
                    "Five",
                    "Six",
                    "Seven",
                    "Eight",
                    "Nine",
                    "Ten"
                ])) {
                    $semesterNumber = 1;
                } else if (in_array($program->name, [
                    "4yrs BBS 2nd Year",
                    "4yrs B.Ed. 2nd Year",
                    "3 Year B.Ed. 2nd Year",
                    "B.A. 2nd Year",
                    "4yrs B.A 2nd Year",
                    "M.Ed 2nd Semster"
                ])) {
                    $semesterNumber = 2;
                } else if (in_array($program->name, [
                    "4yrs BBS 3rd Year",
                    "4yrs B.Ed. 3rd Year",
                    "3 Years B.Ed. 3rd Year",
                    "B.A. 3rd Year",
                    "4yrs B.A 3rd Year",
                    "M.Ed 3rd Semster"
                ])) {
                    $semesterNumber = 3;
                } else if (in_array($program->name, [
                    "4yrs BBS 4th Year",
                    "4yrs B.Ed. 4th Year",
                    "B.A.4th Year",
                    "4yrs B.A 4th Year",
                    "M.Ed 4th Semster"
                ])) {
                    $semesterNumber = 4;
                } else {
                    // Handle cases where program name doesn't match any known pattern
                    continue; // or set a default value
                }

                $program_new = DB::connection('mysql')->table('programs')->where('code', 'LIKE', '%' . $item->CID . '%')->where('name', 'LIKE', '%' . '1' . '%')->first();
                if (!$program_new) {
                    continue;
                }
                $program_semester = DB::connection('mysql')->table('program_semesters')
                    ->where('program_id', $program_new->id)
                    ->where('semester_number', $semesterNumber)
                    ->first();

                $subject = DB::connection('mysql')->table('subjects')->where('sid', $item->SUBID)->first();
                if ($program_semester && $subject) {
                    DB::connection('mysql')->table('program_semester_subjects')->insert([
                        'program_semester_id' => $program_semester->id,
                        'subject_id' => $subject->id,
                        'full_mark' => $item->FMTH,
                        'pass_mark' => $item->PMTH,
                        'type' => "Compulsary",
                    ]);
                }
            }
        }
        return "successfull";
    }
    public function examResult()
    {
        $data = DB::connection('ugc')->table('tblexamresult')->get();
        foreach ($data as $item) {
            $external_exam  = DB::connection('mysql')->table('external_exams')->where('code', $item->SCLASS)->where('exam_year', $item->YEARID)->first();
            if (!$external_exam) {
                continue;
            }
            $student = DB::connection('mysql')->table('students')->where("reg_no", $item->REGNO)->first();
            if (!$student) {
                continue;
            }
            DB::connection('mysql')->table('exam_forms')->where('external_exam_id', $external_exam->id)->where('student_id', $student->id)
                ->update([
                    'year_id' => $external_exam->exam_year,
                    'prog_code' => $external_exam->code,
                    'OM' => $item->OMMARK,
                    'grade_percentage' => $item->OMPER,
                    'result' => $item->RESULTTYPE
                ]);
        }
        return "Successfull";
    }
    public function examMark()
    {
        $data = DB::connection('ugc')->table('tblexammark')->get();
        foreach ($data as $item) {
            $external_exam  = DB::connection('mysql')->table('external_exams')->where('code', $item->EXAMID)->where('exam_year', $item->YEARID)->first();
            if (!$external_exam) {
                continue;
            }
            $student = DB::connection('mysql')->table('students')->where("reg_no", $item->REGNO)->first();
            if (!$student) {
                continue;
            }
            $exam_form = DB::connection('mysql')->table('exam_forms')->where('external_exam_id', $external_exam->id)->where('student_id', $student->id)->first();
            if (in_array($item->EXAMID, ["L018", "L070", "L098", "L068", "L028", "L090"])) {
                $program = DB::connection('mysql')->table('programs')->where('code', 'LIKE', '%' . $item->EXAMID . '%')->first();
            } else {
                $program = DB::connection('mysql')->table('programs')->where('code', $item->EXAMID)->first();
            }
            if ($program) {
                if (in_array($program->name, [
                    "4yrs BBS 1st Year",
                    "4yrs B.Ed. 1st Year",
                    "3 Years B.Ed.1st Year",
                    "1yr B.ED.",
                    "B.A.1ST Year",
                    "4yrs B.A 1st Year",
                    "M.Ed 1st Semester",
                    "11 Eleven",
                    "12 Twelve",
                    "11 Eleven(Hum)",
                    "12 Twelve(Hum)",
                    "11 Eleven(Edu)",
                    "12 Twelve(Edu)",
                    "Eleven",
                    "Twelve",
                    "Nurssary",
                    "LKG",
                    "UKG",
                    "One",
                    "Two",
                    "Three",
                    "Four",
                    "Five",
                    "Six",
                    "Seven",
                    "Eight",
                    "Nine",
                    "Ten"
                ])) {
                    $semesterNumber = 1;
                } else if (in_array($program->name, [
                    "4yrs BBS 2nd Year",
                    "4yrs B.Ed. 2nd Year",
                    "3 Year B.Ed. 2nd Year",
                    "B.A. 2nd Year",
                    "4yrs B.A 2nd Year",
                    "M.Ed 2nd Semster"
                ])) {
                    $semesterNumber = 2;
                } else if (in_array($program->name, [
                    "4yrs BBS 3rd Year",
                    "4yrs B.Ed. 3rd Year",
                    "3 Years B.Ed. 3rd Year",
                    "B.A. 3rd Year",
                    "4yrs B.A 3rd Year",
                    "M.Ed 3rd Semster"
                ])) {
                    $semesterNumber = 3;
                } else if (in_array($program->name, [
                    "4yrs BBS 4th Year",
                    "4yrs B.Ed. 4th Year",
                    "B.A.4th Year",
                    "4yrs B.A 4th Year",
                    "M.Ed 4th Semster"
                ])) {
                    $semesterNumber = 4;
                } else {
                    // Handle cases where program name doesn't match any known pattern
                    continue; // or set a default value
                }

                $program_new = DB::connection('mysql')->table('programs')->where('code', 'LIKE', '%' . $item->EXAMID . '%')->where('name', 'LIKE', '%' . '1' . '%')->first();

                if (!$program_new) {
                    continue;
                }
                $program_semester = DB::connection('mysql')->table('program_semesters')
                    ->where('program_id', $program_new->id)
                    ->where('semester_number', $semesterNumber)
                    ->first();

                $subject = DB::connection('mysql')->table('subjects')->where('sid', $item->SUBID)->first();
                if ($program_semester && $subject) {
                    $program_sem_sub = DB::connection('mysql')->table('program_semester_subjects')->where('program_semester_id', $program_semester->id)->where('subject_id', $subject->id)->first();
                    if ($program_sem_sub && $exam_form) {
                        DB::connection('mysql')->table('external_exam_results')->insert([
                            'exam_form_id' => $exam_form->id,
                            'prog_sub_id' => $program_sem_sub->id,
                            'OM' => $item->OBMARK,
                            'Result' => $item->RESULT
                        ]);
                    }
                }
            }
        }
    }
    public function examMarkUrlabari()
    {
        // Your exact new program list converted to PHP array
        $programConfig = [
            ["name" => "1 MBS First Sem.",          "code" => "L006,L083,L084,L085"],
            ["name" => "1 B.Sc. First Year",        "code" => "L009"],
            ["name" => "1 MBA First Year",          "code" => "L012"],
            ["name" => "11 Eleven",                 "code" => "L014"],
            ["name" => "12 Twelve",                 "code" => "L015"],
            ["name" => "1 M.Sc. First Year",        "code" => "L016"],
            ["name" => "1 BBS 1st Year",            "code" => "L018,L019,L059,L060"],
            ["name" => "BBS 2nd Year",              "code" => "L019"],
            ["name" => "11 Eleven",                 "code" => "L020"],
            ["name" => "12 Twelve",                 "code" => "L021"],
            ["name" => "1 M.A. 1st Year",           "code" => "L022,L047"],
            ["name" => "1 B.A. 1st Year",           "code" => "L024,L044,L045,L046"],
            ["name" => "1 B.Ed. 1st Year",          "code" => "L028,L065,L066,L067"],
            ["name" => "Nurssary",                  "code" => "L031"],
            ["name" => "LKG",                       "code" => "L032"],
            ["name" => "UKG",                       "code" => "L033"],
            ["name" => "One",                       "code" => "L034"],
            ["name" => "Two",                       "code" => "L035"],
            ["name" => "Three",                     "code" => "L036"],
            ["name" => "Four",                      "code" => "L037"],
            ["name" => "Five",                      "code" => "L038"],
            ["name" => "Six",                       "code" => "L039"],
            ["name" => "Seven",                     "code" => "L040"],
            ["name" => "Eight",                     "code" => "L041"],
            ["name" => "Nine",                      "code" => "L042"],
            ["name" => "Ten",                       "code" => "L043"],
            ["name" => "B.A. 2nd Year",             "code" => "L044"],
            ["name" => "B.A. 3rd Year",             "code" => "L045"],
            ["name" => "B.A. 4th Year",             "code" => "L046"],
            ["name" => "M.A. 2nd Year",             "code" => "L047"],
            ["name" => "1 M.A. 1st Semester",       "code" => "L048,L049,L050,L051"],
            ["name" => "M.A. 2nd Semester",        "code" => "L049"],
            ["name" => "M.A. 3rd Semester",         "code" => "L050"],
            ["name" => "M.A. 4th Semester",         "code" => "L051"],
            ["name" => "1 M.ed 1st Year",           "code" => "L053,L054"],
            ["name" => "M.ed. 2nd Year",            "code" => "L054"],
            ["name" => "1 M.ed. 1st Semester",      "code" => "L055,L056,L057,L058"],
            ["name" => "M.ed. 2nd Semester",        "code" => "L056"],
            ["name" => "M.ed. 3rd Semester",        "code" => "L057"],
            ["name" => "M.ed. 4th Semester",      "code" => "L058"],
            ["name" => "BBS 3rd Year",              "code" => "L059"],
            ["name" => "BBS 4th Year",              "code" => "L060"],
            ["name" => "11 Education",              "code" => "L063"],
            ["name" => "12 Education",              "code" => "L064"],
            ["name" => "B.Ed. 2nd Year",            "code" => "L065"],
            ["name" => "B.Ed. 3rd Year",          "code" => "L066"],
            ["name" => "B.Ed. 4th Year",            "code" => "L067"],
            ["name" => "1 Year B.Ed.",              "code" => "L077"],
            ["name" => "ELEVEN 11",                 "code" => "L080"],
            ["name" => "Twelve  ",                  "code" => "L082"],
            ["name" => "MBS Second Sem. ",          "code" => "L083"],
            ["name" => "MBS Third Sem. ",           "code" => "L084"],
            ["name" => "MBS Fourth Sem. ",          "code" => "L085"],
        ];

        // Build lookup: EXAMID => program name
        $examToProgramName = [];
        foreach ($programConfig as $cfg) {
            $codes = array_map('trim', explode(',', $cfg['code']));
            foreach ($codes as $code) {
                if ($code) {
                    $examToProgramName[$code] = $cfg['name'];
                }
            }
        }

        // Determine semester from program name (exactly like your original logic)
        $getSemester = function ($name) {
            $firstYear = [
                "1 MBS First Sem.",
                "1 B.Sc. First Year",
                "1 MBA First Year",
                "11 Eleven",
                "12 Twelve",
                "1 M.Sc. First Year",
                "1 BBS 1st Year",
                "1 M.A. 1st Year",
                "1 B.A. 1st Year",
                "1 B.Ed. 1st Year",
                "Nurssary",
                "LKG",
                "UKG",
                "One",
                "Two",
                "Three",
                "Four",
                "Five",
                "Six",
                "Seven",
                "Eight",
                "Nine",
                "Ten",
                "1 M.A. 1st Semester",
                "1 M.ed 1st Year",
                "1 M.ed. 1st Semester",
                "11 Education",
                "12 Education",
                "1 Year B.Ed.",
                "ELEVEN 11",
                "Twelve  "
            ];

            $secondYear = [
                "BBS 2nd Year",
                "B.A. 2nd Year",
                "M.A. 2nd Year",
                "M.ed. 2nd Year",
                "B.Ed. 2nd Year",
                "M.A. 2nd Semester",
                "M.ed. 2nd Semester",
                "MBS Second Sem. "
            ];

            $thirdYear = [
                "BBS 3rd Year",
                "B.A. 3rd Year",
                "B.Ed. 3rd Year",
                "M.A. 3rd Semester",
                "M.ed. 3rd Semester",
                "MBS Third Sem. "
            ];

            $fourthYear = [
                "BBS 4th Year",
                "B.A. 4th Year",
                "B.Ed. 4th Year",
                "M.A. 4th Semester",
                "M.ed. 4th Semester",
                "MBS Fourth Sem. "
            ];

            if (in_array($name, $firstYear))  return 1;
            if (in_array($name, $secondYear)) return 2;
            if (in_array($name, $thirdYear))  return 3;
            if (in_array($name, $fourthYear)) return 4;

            return null; // unknown → skip
        };

        $data = DB::connection('ugc')->table('tblexammark')->get();

        foreach ($data as $item) {
            $external_exam = DB::connection('mysql')->table('external_exams')
                ->where('code', $item->EXAMID)
                ->where('exam_year', $item->YEARID)
                ->first();

            if (!$external_exam) continue;

            $student = DB::connection('mysql')->table('students')
                ->where("reg_no", $item->REGNO)
                ->first();

            if (!$student) continue;

            $exam_form = DB::connection('mysql')->table('exam_forms')
                ->where('external_exam_id', $external_exam->id)
                ->where('student_id', $student->id)
                ->first();

            // === NEW: Use your exact program list to get program name ===
            $programName = $examToProgramName[$item->EXAMID] ?? null;

            if (!$programName) continue;

            // === Determine semester exactly like your old code ===
            $semesterNumber = $getSemester($programName);
            if (is_null($semesterNumber)) continue;

            // === Same as your original: special LIKE for some codes ===
            if (in_array($item->EXAMID, ["L006", "L018", "L024", "L048", "L028", "L053", "L055"])) {
                $program = DB::connection('mysql')->table('programs')
                    ->where('code', 'LIKE', '%' . $item->EXAMID . '%')
                    ->first();
            } else {
                $program = DB::connection('mysql')->table('programs')
                    ->where('code', $item->EXAMID)
                    ->first();
            }

            if (!$program) continue;

            // === This line is exactly from your code ===
            $program_new = DB::connection('mysql')->table('programs')
                ->where('code', 'LIKE', '%' . $item->EXAMID . '%')
                ->where('name', 'LIKE', '%' . '1' . '%')
                ->first();

            if (!$program_new) continue;

            $program_semester = DB::connection('mysql')->table('program_semesters')
                ->where('program_id', $program_new->id)
                ->where('semester_number', $semesterNumber)
                ->first();

            $subject = DB::connection('mysql')->table('subjects')
                ->where('sid', $item->SUBID)
                ->first();

            if ($program_semester && $subject) {
                $program_sem_sub = DB::connection('mysql')->table('program_semester_subjects')
                    ->where('program_semester_id', $program_semester->id)
                    ->where('subject_id', $subject->id)
                    ->first();

                if ($program_sem_sub && $exam_form) {
                    DB::connection('mysql')->table('external_exam_results')->insert([
                        'exam_form_id' => $exam_form->id,
                        'prog_sub_id'  => $program_sem_sub->id,
                        'OM'           => $item->OBMARK,
                        'Result'       => $item->RESULT
                    ]);
                }
            }
        }
    }
    public function examMarkneb()
    {
        $data = DB::connection('ugc')->table('tblexammarkneb')->get();
        foreach ($data as $item) {
            $external_exam  = DB::connection('mysql')->table('external_exams')->where('code', $item->EXID)->where('exam_year', $item->ACAID)->first();
            if (!$external_exam) {
                continue;
            }
            $student = DB::connection('mysql')->table('students')->where("reg_no", $item->REGNO)->first();
            if (!$student) {
                continue;
            }
            $exam_form = DB::connection('mysql')->table('exam_forms')->where('external_exam_id', $external_exam->id)->where('student_id', $student->id)->first();
            if (in_array($item->EXID, ["L018", "L070", "L098", "L068", "L028", "L090"])) {
                $program = DB::connection('mysql')->table('programs')->where('code', 'LIKE', '%' . $item->EXID . '%')->first();
            } else {
                $program = DB::connection('mysql')->table('programs')->where('code', $item->EXID)->first();
            }
            if ($program) {
                if (in_array($program->name, [
                    "4yrs BBS 1st Year",
                    "4yrs B.Ed. 1st Year",
                    "3 Years B.Ed.1st Year",
                    "1yr B.ED.",
                    "B.A.1ST Year",
                    "4yrs B.A 1st Year",
                    "M.Ed 1st Semester",
                    "11 Eleven",
                    "12 Twelve",
                    "11 Eleven(Hum)",
                    "12 Twelve(Hum)",
                    "11 Eleven(Edu)",
                    "12 Twelve(Edu)",
                    "Eleven",
                    "Twelve",
                    "Nurssary",
                    "LKG",
                    "UKG",
                    "One",
                    "Two",
                    "Three",
                    "Four",
                    "Five",
                    "Six",
                    "Seven",
                    "Eight",
                    "Nine",
                    "Ten"
                ])) {
                    $semesterNumber = 1;
                } else if (in_array($program->name, [
                    "4yrs BBS 2nd Year",
                    "4yrs B.Ed. 2nd Year",
                    "3 Year B.Ed. 2nd Year",
                    "B.A. 2nd Year",
                    "4yrs B.A 2nd Year",
                    "M.Ed 2nd Semster"
                ])) {
                    $semesterNumber = 2;
                } else if (in_array($program->name, [
                    "4yrs BBS 3rd Year",
                    "4yrs B.Ed. 3rd Year",
                    "3 Years B.Ed. 3rd Year",
                    "B.A. 3rd Year",
                    "4yrs B.A 3rd Year",
                    "M.Ed 3rd Semster"
                ])) {
                    $semesterNumber = 3;
                } else if (in_array($program->name, [
                    "4yrs BBS 4th Year",
                    "4yrs B.Ed. 4th Year",
                    "B.A.4th Year",
                    "4yrs B.A 4th Year",
                    "M.Ed 4th Semster"
                ])) {
                    $semesterNumber = 4;
                } else {
                    // Handle cases where program name doesn't match any known pattern
                    continue; // or set a default value
                }

                $program_new = DB::connection('mysql')->table('programs')->where('code', 'LIKE', '%' . $item->EXID . '%')->where('name', 'LIKE', '%' . '1' . '%')->first();

                if (!$program_new) {
                    continue;
                }
                $program_semester = DB::connection('mysql')->table('program_semesters')
                    ->where('program_id', $program_new->id)
                    ->where('semester_number', $semesterNumber)
                    ->first();

                $subject = DB::connection('mysql')->table('subjects')->where('sid', $item->SUBID)->first();
                if ($program_semester && $subject) {
                    $program_sem_sub = DB::connection('mysql')->table('program_semester_subjects')->where('program_semester_id', $program_semester->id)->where('subject_id', $subject->id)->first();
                    if ($program_sem_sub && $exam_form) {
                        DB::connection('mysql')->table('external_exam_results')->insert([
                            'exam_form_id' => $exam_form->id,
                            'prog_sub_id' => $program_sem_sub->id,
                            'OM' => $item->OM,
                        ]);
                    }
                }
            }
        }
    }
    public function programSubjectsneb()
    {

        // First get unique combinations
        $data = DB::connection('ugc')
            ->table('tblexamsubject_neb')->get();

        $programSemesterMap = [
            // ==== BBS ====
            "4yrs BBS 1st Year" => 1,
            "4yrs BBS 2nd Year" => 2,
            "4yrs BBS 3rd Year" => 3,
            "4yrs BBS 4th Year" => 4,

            // ==== B.Ed ====
            "4yrs B.Ed. 1st Year" => 1,
            "4yrs B.Ed. 2nd Year" => 2,
            "4yrs B.Ed. 3rd Year" => 3,
            "4yrs B.Ed. 4th Year" => 4,

            "3 Years B.Ed.1st Year" => 1,
            "3 Year B.Ed. 2nd Year" => 2,
            "3 Years B.Ed. 3rd Year" => 3,

            "1yr B.ED." => 1,

            // ==== B.A. ====
            "B.A.1ST Year" => 1,
            "B.A. 2nd Year" => 2,
            "B.A. 3rd Year" => 3,
            "B.A.4th Year" => 4,

            "4yrs B.A 1st Year" => 1,
            "4yrs B.A 2nd Year" => 2,
            "4yrs B.A 3rd Year" => 3,
            "4yrs B.A 4th Year" => 4,

            // ==== M.Ed ====
            "M.Ed 1st Semester" => 1,
            "M.Ed 2nd Semster"  => 2,
            "M.Ed 3rd Semster"  => 3,
            "M.Ed 4th Semster"  => 4,

            // ==== +2 ====
            "11 Eleven" => 1,
            "12 Twelve" => 1,
            "11 Eleven(Hum)" => 1,
            "12 Twelve(Hum)" => 1,
            "11 Eleven(Edu)" => 1,
            "12 Twelve(Edu)" => 1,
            "Eleven"    => 1,
            "Twelve"    => 1,

            // ==== School levels (all semester_number = 1) ====
            "Nurssary" => 1,
            "LKG"      => 1,
            "UKG"      => 1,
            "One"      => 1,
            "Two"      => 1,
            "Three"    => 1,
            "Four"     => 1,
            "Five"     => 1,
            "Six"      => 1,
            "Seven"    => 1,
            "Eight"    => 1,
            "Nine"     => 1,
            "Ten"      => 1,
        ];
        foreach ($data as $item) {
            if (in_array($item->CID, ["L018", "L070", "L098", "L068", "L028", "L090"])) {
                $program = DB::connection('mysql')->table('programs')->where('code', 'LIKE', '%' . $item->CID . '%')->first();
            } else {
                $program = DB::connection('mysql')->table('programs')->where('code', $item->CID)->first();
            }
            if ($program) {
                if (in_array($program->name, [
                    "4yrs BBS 1st Year",
                    "4yrs B.Ed. 1st Year",
                    "3 Years B.Ed.1st Year",
                    "1yr B.ED.",
                    "B.A.1ST Year",
                    "4yrs B.A 1st Year",
                    "M.Ed 1st Semester",
                    "11 Eleven",
                    "12 Twelve",
                    "11 Eleven(Hum)",
                    "12 Twelve(Hum)",
                    "11 Eleven(Edu)",
                    "12 Twelve(Edu)",
                    "Eleven",
                    "Twelve",
                    "Nurssary",
                    "LKG",
                    "UKG",
                    "One",
                    "Two",
                    "Three",
                    "Four",
                    "Five",
                    "Six",
                    "Seven",
                    "Eight",
                    "Nine",
                    "Ten"
                ])) {
                    $semesterNumber = 1;
                } else if (in_array($program->name, [
                    "4yrs BBS 2nd Year",
                    "4yrs B.Ed. 2nd Year",
                    "3 Year B.Ed. 2nd Year",
                    "B.A. 2nd Year",
                    "4yrs B.A 2nd Year",
                    "M.Ed 2nd Semster"
                ])) {
                    $semesterNumber = 2;
                } else if (in_array($program->name, [
                    "4yrs BBS 3rd Year",
                    "4yrs B.Ed. 3rd Year",
                    "3 Years B.Ed. 3rd Year",
                    "B.A. 3rd Year",
                    "4yrs B.A 3rd Year",
                    "M.Ed 3rd Semster"
                ])) {
                    $semesterNumber = 3;
                } else if (in_array($program->name, [
                    "4yrs BBS 4th Year",
                    "4yrs B.Ed. 4th Year",
                    "B.A.4th Year",
                    "4yrs B.A 4th Year",
                    "M.Ed 4th Semster"
                ])) {
                    $semesterNumber = 4;
                } else {
                    // Handle cases where program name doesn't match any known pattern
                    continue; // or set a default value
                }

                $program_new = DB::connection('mysql')->table('programs')->where('code', 'LIKE', '%' . $item->CID . '%')->where('name', 'LIKE', '%' . '1' . '%')->first();
                if (!$program_new) {
                    continue;
                }
                $program_semester = DB::connection('mysql')->table('program_semesters')
                    ->where('program_id', $program_new->id)
                    ->where('semester_number', $semesterNumber)
                    ->first();

                $subject = DB::connection('mysql')->table('subjects')->where('sid', $item->SUBID)->first();
                if ($program_semester && $subject) {
                    DB::connection('mysql')->table('program_semester_subjects')->insert([
                        'program_semester_id' => $program_semester->id,
                        'subject_id' => $subject->id,
                        'full_mark' => $item->FMTH,
                        'pass_mark' => $item->PMTH,
                        'type' => "Compulsary",
                    ]);
                }
            }
        }
        return "successfull";
    }
    public function resolveYear()
    {
        $data = DB::connection('mysql')->table('external_exams')->get();
        foreach ($data as $item) {
            $result = DB::connection('ugc')->table('tblexamresult')->where('YEARID', $item->exam_year)->first();
            if ($result) {
                $external_exams = DB::connection('mysql')->table('external_exams')->where('exam_year', $item->exam_year)->get();
                foreach ($external_exams as $exam) {
                    DB::connection('mysql')->table('external_exams')->where('id', $exam->id)
                        ->update([
                            'yearid' => $result->EXAMYEAR,
                        ]);
                }
            }
        }
        return "successfull";
    }
    public function resolvePartial()
    {
        try {
            $data = DB::connection('ugc')->table('tblexamresult')->where('EXAMTYPE', 1)->get();

            $processedCount = 0;

            foreach ($data as $item) {
                // Skip if REGNO is empty/null
                if (empty($item->REGNO)) {
                    continue;
                }

                $student = DB::connection('mysql')->table('students')->where('reg_no', $item->REGNO)->first();
                if (!$student) {
                    continue;
                }

                // Skip if SCLASS is empty/null
                if (empty($item->SCLASS)) {
                    continue;
                }

                $parent_data = DB::connection('mysql')
                    ->table('exam_forms')
                    ->where('student_id', $student->id)
                    ->where('prog_code', $item->SCLASS)
                    ->orderBy('year_id', 'asc')
                    ->first();

                if (!$parent_data) {
                    continue;
                }

                // Fixed: Check count() instead of truthy check on collection
                $all_data = DB::connection('mysql')->table('exam_forms')
                    ->where('student_id', $student->id)
                    ->where('prog_code', $item->SCLASS)
                    ->where('id', '!=', $parent_data->id)
                    ->get();

                if ($all_data->count() == 0) {
                    continue;
                }

                // Batch update for better performance
                $idsToUpdate = $all_data->pluck('id')->toArray();

                DB::connection('mysql')->table('exam_forms')
                    ->whereIn('id', $idsToUpdate)
                    ->update([
                        'parent_id' => $parent_data->id,
                    ]);

                $processedCount++;
            }

            return response()->json([
                'status' => 'success',
                'message' => "Successfully processed {$processedCount} records",
                'processed_count' => $processedCount
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in resolvePartial: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the data'
            ], 500);
        }
    }
    public function getResultall()
    {
        $data = DB::connection('ugc')
            ->table('tblexamresult as r')
            ->join('tblexammark as m', function ($join) {
                $join->on('r.REGNO', '=', 'm.REGNO')
                    ->on('r.SCLASS', '=', 'm.EXAMID')
                    ->on('r.YEARID', '=', 'm.YEARID');
            })
            ->where('r.examtype', 1)
            ->select('r.*', 'm.*')
            ->count();
        return $data;
    }
    public function getLatestResult()
    {
        $result = DB::connection('mysql')
            ->table('exam_forms')
            ->select('exam_forms.*')
            ->join(
                DB::raw('(SELECT parent_id, prog_code, MAX(CAST(SUBSTRING(year_id, 2) AS UNSIGNED)) as max_year
                          FROM exam_forms
                          WHERE parent_id IS NOT NULL AND prog_code IS NOT NULL
                          GROUP BY parent_id, prog_code) latest'),
                function ($join) {
                    $join->on('exam_forms.parent_id', '=', 'latest.parent_id')
                        ->on('exam_forms.prog_code', '=', 'latest.prog_code')
                        ->whereRaw('CAST(SUBSTRING(exam_forms.year_id, 2) AS UNSIGNED) = latest.max_year');
                }
            )
            ->whereNotNull('exam_forms.parent_id')
            ->whereNotNull('exam_forms.prog_code')
            ->get();

        foreach ($result as $data) {
            // Count how many external results are "F"
            $failCount = DB::connection('mysql')
                ->table('external_exam_results')
                ->where('exam_form_id', $data->id)
                ->where('Result', 'F')   // ✅ lowercase column name
                ->count();
            $student = DB::connection('mysql')->table('students')->where('id', $data->student_id)->first();
            $data =  DB::connection('mysql')
                ->table('exam_forms')
                ->where('id', $data->id)->first();
            \Log::info($student->first_name . ' ' . $student->last_name . ' ' . $data->result . ' ' . $data->symbol_no);

            DB::connection('mysql')
                ->table('exam_forms')
                ->where('id', $data->id)
                ->update([
                    "result" => $failCount > 0 ? "Failed" : "Passed",
                ]);
            $newdata =  DB::connection('mysql')
                ->table('exam_forms')
                ->where('id', $data->id)->first();
            \Log::info($student->first_name . ' ' . $student->last_name . ' ' . $newdata->result . ' ' . $newdata->symbol_no);
        }
        return "Successful";
    }
    public function allPrograms()
    {
        $programs = DB::connection('mysql')->table('programs')->select('name')->get();
        return response()->json($programs);
    }
    public function manageStudentAcademicUrlabari()
    {
        // Program mapping from the provided list
        $programSemesterMap = [
            // ==== MBS ====
            "MBS 1st Sem."   => 1,
            "MBS Second Sem."  => 2,
            "MBS Third Sem."   => 3,
            "MBS Fourth Sem."  => 4,

            // ==== B.Sc. ====
            "B.Sc. 1st Year" => 1,

            // ==== MBA ====
            "MBA 1st Year"   => 1,

            // ==== +2 ====
            "11 Eleven"        => 1,
            "12 Twelve"        => 1,

            // ==== M.Sc. ====
            "M.Sc. 1st Year" => 1,

            // ==== BBS ====
            "BBS 1st Year"     => 1,
            "BBS 2nd Year"     => 2,
            "BBS 3rd Year"     => 3,
            "BBS 4th Year"     => 4,

            // ==== Education +2 ====
            "11 Education" => 1,
            "12 Education" => 1,

            // ==== M.A. ====
            "M.A. 1st Year" => 1,
            "M.A. 2nd Year" => 2,
            "M.A. 1st Semester" => 1,
            "M.A. 2nd Semester" => 2,
            "M.A. 3rd Semester" => 3,
            "M.A. 4th Semester" => 4,

            // ==== B.A. ====
            "B.A. 1st Year" => 1,
            "B.A. 2nd Year" => 2,
            "B.A. 3rd Year" => 3,
            "B.A. 4th Year" => 4,

            // ==== B.Ed. ====
            "B.Ed. 1st Year" => 1,
            "B.Ed. 2nd Year" => 2,
            "B.Ed. 3rd Year" => 3,
            "B.Ed. 4th Year" => 4,
            "1 Year B.Ed."   => 1,

            // ==== M.Ed. ====
            "M.ed 1st Year" => 1,
            "M.ed. 2nd Year" => 2,
            "M.ed. 1st Semester" => 1,
            "M.ed. 2nd Semester" => 2,
            "M.ed. 3rd Semester" => 3,
            "M.ed. 4th Semester" => 4,

            // ==== Extra +2 ====
            "ELEVEN 11" => 1,
            "Twelve"    => 1,
        ];

        $students = DB::connection('mysql')->table('student_academic_records')->get();

        // Fetch first programs from DB (only names from your JSON)
        $firstPrograms = [];
        foreach ($programSemesterMap as $name => $sem) {
            // First-year/first-semester references only
            if (
                str_contains($name, "1st Sem") ||
                str_contains($name, "First Year") ||
                str_contains($name, "1st Year") ||
                str_contains($name, "1st Semester")
            ) {
                $firstPrograms[$name] = DB::connection('mysql')->table('programs')->where('name', $name)->first();
            }
        }

        foreach ($students as $student) {

            $program = DB::connection('mysql')->table('programs')->where('id', $student->program_id)->first();
            if (!$program) continue;

            $name = trim($program->name);

            if (!isset($programSemesterMap[$name])) continue;

            $semesterNumber = $programSemesterMap[$name];

            // determine group first program
            $groupFirst = null;

            // Identify program groups using name patterns
            if (str_starts_with($name, "MBS")) {
                $groupFirst = $firstPrograms["MBS 1st Sem."] ?? null;
            } elseif (str_starts_with($name, "B.Sc")) {
                $groupFirst = $firstPrograms["B.Sc. 1st Year"] ?? null;
            } elseif (str_starts_with($name, "MBA")) {
                $groupFirst = $firstPrograms["MBA 1st Year"] ?? null;
            } elseif (str_starts_with($name, "M.Sc")) {
                $groupFirst = $firstPrograms["M.Sc. 1st Year"] ?? null;
            } elseif (str_starts_with($name, "BBS")) {
                $groupFirst = $firstPrograms["BBS 1st Year"] ?? null;
            } elseif (str_starts_with($name, "M.A.")) {
                if (str_contains($name, "Semester")) {
                    $groupFirst = $firstPrograms["M.A. 1st Semester"] ?? null;
                } else {
                    $groupFirst = $firstPrograms["M.A. 1st Year"] ?? null;
                }
            } elseif (str_starts_with($name, "B.A.")) {
                $groupFirst = $firstPrograms["B.A. 1st Year"] ?? null;
            } elseif (str_starts_with($name, "B.Ed")) {
                $groupFirst = $firstPrograms["B.Ed. 1st Year"] ?? null;
            } elseif (str_starts_with($name, "1 Year B.Ed")) {
                $groupFirst = $firstPrograms["1 Year B.Ed."] ?? null;
            } elseif (str_starts_with($name, "M.ed")) {
                if (str_contains($name, "Semester")) {
                    $groupFirst = $firstPrograms["M.ed. 1st Semester"] ?? null;
                } else {
                    $groupFirst = $firstPrograms["M.ed 1st Year"] ?? null;
                }
            } elseif ($name === "11 Eleven") {
                $groupFirst = $firstPrograms["11 Eleven"] ?? null;
            } elseif ($name === "12 Twelve") {
                $groupFirst = $firstPrograms["12 Twelve"] ?? null;
            } elseif ($name === "11 Education") {
                $groupFirst = $firstPrograms["11 Education"] ?? null;
            } elseif ($name === "12 Education") {
                $groupFirst = $firstPrograms["12 Education"] ?? null;
            } elseif ($name === "ELEVEN 11") {
                $groupFirst = $firstPrograms["ELEVEN 11"] ?? null;
            } elseif (str_starts_with($name, "Twelve")) {
                $groupFirst = $firstPrograms["Twelve"] ?? null;
            }

            if (!$groupFirst) continue;

            // Get semester row
            $semesterData = DB::connection('mysql')
                ->table('program_semesters')
                ->where('program_id', $groupFirst->id)
                ->where('semester_number', $semesterNumber)
                ->first();

            if ($semesterData) {
                DB::connection('mysql')
                    ->table('student_academic_records')
                    ->where('id', $student->id)
                    ->update([
                        "program_id" => $semesterData->program_id,
                        "program_semester_id" => $semesterData->id
                    ]);
            }
        }

        return "successful";
    }

    public function mergeLevels()
    {
        $levels = DB::connection('mysql')
            ->table('levels')
            ->select('title', DB::raw('MIN(id) as id'))
            ->groupBy('title')
            ->get();

        foreach ($levels as $level) {
            $duplicateLevels = DB::connection('mysql')
                ->table('levels')
                ->where('title', $level->title)
                ->where('id', '!=', $level->id)
                ->pluck('id');

            if ($duplicateLevels->isEmpty()) {
                continue;
            }
            $slugs = DB::connection('mysql')
                ->table('levels')
                ->whereIn('id', $duplicateLevels)
                ->pluck('slug')
                ->implode(',');

            DB::connection('mysql')
                ->table('levels')
                ->where('id', $level->id)
                ->update(['slug' => DB::raw("CONCAT(slug, ',', '$slugs')")]);

            DB::connection('mysql')
                ->table('levels')
                ->whereIn('id', $duplicateLevels)
                ->delete();
        }
    }
    public function mergePrograms()
    {
        $programs = DB::table('programs')->get();

        foreach ($programs as $program) {

            $name = $program->name;

            // Extract prefix: e.g., "MBS", "MBA", "BBS", "B.Ed", "M.Ed", etc.
            preg_match('/^[A-Za-z\. ]+/', $name, $prefixMatch);
            if (!isset($prefixMatch[0])) continue;

            $prefix = trim($prefixMatch[0]);

            // CASE 1: FIRST SEMESTER
            if (stripos($name, 'First Sem') !== false) {

                // find related semesters (2nd, 3rd, 4th)
                $related = DB::table('programs')
                    ->where('name', 'like', $prefix . '%Sem%')
                    ->where('id', '!=', $program->id)
                    ->pluck('code');

                if ($related->count()) {
                    $merged = $program->code . ',' . $related->implode(',');

                    DB::table('programs')
                        ->where('id', $program->id)
                        ->update(['code' => $merged]);

                    DB::table('programs')
                        ->where('name', 'like', $prefix . '%Sem%')
                        ->where('id', '!=', $program->id)
                        ->delete();
                }
            }

            // CASE 2: FIRST YEAR
            if (stripos($name, 'First Year') !== false || stripos($name, '1st Year') !== false) {

                // find related years (2nd, 3rd, 4th)
                $related = DB::table('programs')
                    ->where('name', 'like', $prefix . '%Year%')
                    ->where('id', '!=', $program->id)
                    ->pluck('code');

                if ($related->count()) {
                    $merged = $program->code . ',' . $related->implode(',');

                    DB::table('programs')
                        ->where('id', $program->id)
                        ->update(['code' => $merged]);

                    DB::table('programs')
                        ->where('name', 'like', $prefix . '%Year%')
                        ->where('id', '!=', $program->id)
                        ->delete();
                }
            }
        }

        return response()->json(['message' => 'Merge completed successfully']);
    }
    public function programSubjectsUrlabari()
    {
        DB::connection('mysql')->table('program_semester_subjects')->truncate();

        // Load source data
        $data = DB::connection('ugc')->table('tblexamsubject')->get();

        // Urlabari program semester map
        $programSemesterMap = [
            // ==== MBS ====
            "MBS 1st Sem." => 1,
            "MBS Second Sem." => 2,
            "MBS Third Sem." => 3,
            "MBS Fourth Sem." => 4,

            // ==== B.Sc ====
            "B.Sc. 1st Year" => 1,

            // ==== MBA ====
            "MBA 1st Year" => 1,

            // ==== M.Sc ====
            "M.Sc. 1st Year" => 1,

            // ==== BBS ====
            "BBS 1st Year" => 1,
            "BBS 2nd Year" => 2,
            "BBS 3rd Year" => 3,
            "BBS 4th Year" => 4,

            // ==== +2 ====
            "11 Eleven" => 1,
            "12 Twelve" => 1,
            "11 Education" => 1,
            "12 Education" => 1,
            "ELEVEN 11" => 1,
            "Twelve" => 1,

            // ==== M.A ====
            "M.A. 1st Year" => 1,
            "M.A. 2nd Year" => 2,
            "M.A. 1st Semester" => 1,
            "M.A. 2nd Semester" => 2,
            "M.A. 3rd Semester" => 3,
            "M.A. 4th Semester" => 4,

            // ==== B.A ====
            "B.A. 1st Year" => 1,
            "B.A. 2nd Year" => 2,
            "B.A. 3rd Year" => 3,
            "B.A. 4th Year" => 4,

            // ==== B.Ed ====
            "B.Ed. 1st Year" => 1,
            "B.Ed. 2nd Year" => 2,
            "B.Ed. 3rd Year" => 3,
            "B.Ed. 4th Year" => 4,
            "1 Year B.Ed." => 1,

            // ==== M.Ed ====
            "M.ed 1st Year" => 1,
            "M.ed. 2nd Year" => 2,
            "M.ed. 1st Semester" => 1,
            "M.ed. 2nd Semester" => 2,
            "M.ed. 3rd Semester" => 3,
            "M.ed. 4th Semester" => 4,

            // ==== School ====
            "Nurssary" => 1,
            "LKG" => 1,
            "UKG" => 1,
            "One" => 1,
            "Two" => 1,
            "Three" => 1,
            "Four" => 1,
            "Five" => 1,
            "Six" => 1,
            "Seven" => 1,
            "Eight" => 1,
            "Nine" => 1,
            "Ten" => 1,
        ];

        foreach ($data as $item) {

            // Match program using CID
            if (in_array($item->CID, ["L006", "L018","L022", "L024", "L048", "L028", "L053", "L055"])) {
                $program = DB::connection('mysql')
                    ->table('programs')
                    ->where('code', 'LIKE', '%' . $item->CID . '%')
                    ->first();
            } else {
                $program = DB::connection('mysql')
                    ->table('programs')
                    ->where('code', $item->CID)
                    ->first();
            }
            if (!$program) continue;
            // Determine semester number
            if (isset($programSemesterMap[$program->name])) {
                $semesterNumber = $programSemesterMap[$program->name];
            } else {
                continue;
            }

            // Get FIRST version of program (only 1st year/1st sem)
            $program_first = DB::connection('mysql')
                ->table('programs')
                ->where('code', 'LIKE', '%' . $item->CID . '%')
                ->where('name', 'LIKE', '%1%')
                ->first();

            if (!$program_first) continue;

            // Find semester row
            $program_semester = DB::connection('mysql')
                ->table('program_semesters')
                ->where('program_id', $program_first->id)
                ->where('semester_number', $semesterNumber)
                ->first();

            if (!$program_semester) continue;

            // Match subject
            $subject = DB::connection('mysql')
                ->table('subjects')
                ->where('sid', $item->SUBID)
                ->first();

            if (!$subject) continue;

            // Insert
            DB::connection('mysql')->table('program_semester_subjects')->insert([
                'program_semester_id' => $program_semester->id,
                'subject_id' => $subject->id,
                'full_mark' => $item->FMTH,
                'pass_mark' => $item->PMTH,
                'type' => "Compulsary"
            ]);
        }

        return "successful";
    }
   
    public function manageExternalExamUrlabari()
    {
        $data = DB::connection('mysql')->table('external_exams')->get();
        foreach ($data as $item) {
            if (in_array($item->code, ["L006", "L018","L022", "L024", "L048", "L028", "L053", "L055"])) {
                $program = DB::connection('mysql')->table('programs')->where('code', 'LIKE', '%' . $item->code . '%')->first();
            } else {
                $program = DB::connection('mysql')->table('programs')->where('code', $item->code)->first();
            }
            if ($program) {
                if (in_array($program->name, [
                    "MBS 1st Sem.",
                    "B.Sc. 1st Year",
                    "MBA 1st Year",
                    "M.Sc. 1st Year",
                    "BBS 1st Year",
                    "M.A. 1st Year",
                    "B.A. 1st Year",
                    "B.Ed. 1st Year",

                    "M.A. 1st Semester",

                    "M.ed 1st Year",
                    "M.ed. 1st Semester",


                    "1 Year B.Ed.",


                ])) {
                    $semesterNumber = 1;
                } else if (in_array($program->name, [
                    "MBS Second Sem.",
                    "BBS 2nd Year",
                    "B.A. 2nd Year",
                    "M.A. 2nd Year",
                    "B.Ed. 2nd Year",
                    "M.A. 2nd Semester",
                    "M.ed. 2nd Year",
                    "M.ed. 2nd Semester",
                ])) {
                    $semesterNumber = 2;
                } else if (in_array($program->name, [
                    "MBS Third Sem.",
                    "BBS 3rd Year",
                    "B.A. 3rd Year",
                    "M.A. 3rd Semester",
                    "B.Ed. 3rd Year",
                    "M.ed. 3rd Semester",
                ])) {
                    $semesterNumber = 3;
                } else if (in_array($program->name, [
                    "MBS Fourth Sem.",
                    "BBS 4th Year",
                    "B.A. 4th Year",
                    "M.A. 4th Semester",
                    "B.Ed. 4th Year",
                    "M.ed. 4th Semester",
                ])) {
                    $semesterNumber = 4;
                } else {
                    // Handle cases where program name doesn't match any known pattern
                    continue; // or set a default value
                }
                $program_new = DB::connection('mysql')->table('programs')->where('code', 'LIKE', '%' . $item->code . '%')->where('name', 'LIKE', '%' . '1' . '%')->first();
                if (!$program_new) {
                    continue;
                }
                // return response()->json($program_new);
                $program_sem = DB::connection('mysql')->table('program_semesters')->where('program_id', $program_new->id)->where('semester_number', $semesterNumber)->first();
                if ($program_sem) {
                    DB::connection('mysql')->table('external_exams')->where('id', $item->id)->update(
                        [
                            'program_id' => $program_sem->program_id,
                            'semester_id' => $program_sem->id,
                        ]
                    );
                }
            }
        }
    }
}

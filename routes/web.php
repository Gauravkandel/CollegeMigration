<?php

use App\Http\Controllers\MigrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('migration')->controller(MigrationController::class)->group(function () {
    Route::get('/academicYears', 'academicYears');
    Route::get('/subjects', 'subjects');
    Route::get('/new_students', 'new_students');
    Route::get('/students', 'students');
    Route::get('/positions', 'positions');
    Route::get('/category', 'category');
    Route::get('/jobType', 'jobType');
    Route::get('/staff', 'staff');
    Route::get('/infrastructureTypes', 'infrastructureTypes');
    Route::get('/faculty', 'faculty');
    Route::get('/level', 'level');
    Route::get('/programs', 'programs');
    Route::get('/studentAcademicRecord', 'studentAcademicRecord');
    Route::get('/new_studentAcademicRecord', 'new_studentAcademicRecord');
    Route::get('/manageStudentAcademic', 'manageStudentAcademic');
    Route::get('/manageStudentAcademicUrlabari', 'manageStudentAcademicUrlabari');
    Route::get('/registerStudents', 'registerStudents');
    Route::get('/externalExam', 'externalExam');
    Route::get('/examForm', 'examForm');
    Route::get('/programSubjects', 'programSubjects');
    Route::get('/manageExternalExam', 'manageExternalExam');
    Route::get('/examResult', 'examResult');
    Route::get('/examMark', 'examMark');
    Route::get('/resolveYear', 'resolveYear');
    Route::get('/examMarkneb', 'examMarkneb');
    Route::get('/programSubjectsneb', 'programSubjectsneb');
    Route::get('/resolvePartial', 'resolvePartial');
    Route::get('/getResultall', 'getResultall');
    Route::get('/getLatestResult', 'getLatestResult');
    Route::get('/allPrograms', 'allPrograms');
    Route::get('/mergeLevels', 'mergeLevels');
    Route::get('/mergePrograms', 'mergePrograms');
    Route::get('/programSubjectsUrlabari', 'programSubjectsUrlabari');
    Route::get('/manageExternalExamUrlabari', 'manageExternalExamUrlabari');
    Route::get('/examMarkUrlabari', 'examMarkUrlabari');
});

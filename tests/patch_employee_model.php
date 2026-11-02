<?php
$file = __DIR__ . '/../app/Models/Employee.php';
$content = file_get_contents($file);

$targetFillable = "    protected array \$fillable = [
        'employee_code',
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'alternate_phone',
        'email',
        'department',
        'designation',
        'employment_type',
        'joining_date',
        'status',
        'emergency_contact',
        'address',
        'created_at',
        'updated_at',
    ];";

$newFillable = "    protected array \$fillable = [
        'employee_code',
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'alternate_phone',
        'email',
        'profile_photo',
        'date_of_birth',
        'gender',
        'nationality',
        'department',
        'designation',
        'employment_type',
        'joining_date',
        'status',
        'emergency_contact',
        'emergency_contact_name',
        'emergency_contact_phone',
        'address',
        'address_line',
        'city',
        'state',
        'postal_code',
        'country',
        'id_type',
        'id_number',
        'id_expiry',
        'id_document_file',
        'license_document_file',
        'notes',
        'created_at',
        'updated_at',
    ];";

$targetSql = "COALESCE(dp.rating, 5.0) as rating";
$newSql = "COALESCE(dp.rating, 5.0) as rating,
                       COALESCE(e.license_document_file, dp.license_document_file) as driver_license_doc";

$cNorm = str_replace("\r\n", "\n", $content);
$tFNorm = str_replace("\r\n", "\n", $targetFillable);
$nFNorm = str_replace("\r\n", "\n", $newFillable);

if (strpos($cNorm, $tFNorm) !== false) {
    $cNorm = str_replace($tFNorm, $nFNorm, $cNorm);
    $cNorm = str_replace($targetSql, $newSql, $cNorm);
    if (strpos($content, "\r\n") !== false) {
        $cNorm = str_replace("\n", "\r\n", $cNorm);
    }
    file_put_contents($file, $cNorm);
    echo "SUCCESS: Employee.php updated successfully!\n";
} else {
    echo "ERROR: Target fillable not found in Employee.php\n";
}

<?php
$file = __DIR__ . '/../app/Controllers/EmployeeController.php';
$content = file_get_contents($file);

$target = '    public function edit(Request $request, array $params): Response
    {
        $id = (int)($params[\'id\'] ?? 0);
        $employee = $this->employeeModel->findWithDetails($id);
        if (!$employee) {
            return $this->redirect(url(\'/office/employees\'), \'danger\', \'Employee not found.\');
        }

        $unlinkedUsers = $this->userModel->where("`role` IN (\'admin\', \'dispatcher\', \'driver\') AND `status` = \'active\'", [], \'name ASC\');

        return $this->render(\'office/employees/edit\', [
            ' . "'employee'      => \$employee,
            'unlinkedUsers' => \$unlinkedUsers,
            'errors'        => Session::getFlash('form_errors') ?? [],
            'title'         => \"Edit Employee - {\$employee['employee_code']}\",
        ], 'layouts/office');
    }

    /**
     * Update existing employee record
     */
    public function update(Request \$request, array \$params): Response
    {
        \$id = (int)(\$params['id'] ?? 0);
        \$employee = \$this->employeeModel->find(\$id);
        if (!\$employee) {
            return \$this->redirect(url('/office/employees'), 'danger', 'Employee not found.');
        }

        try {
            \$input = \$request->all();

            \$data = \$this->validate(\$input, [
                'first_name'      => 'required|min:2|max:80',
                'last_name'       => 'required|min:1|max:80',
                'phone'           => 'required|min:8|max:30',
                'department'      => 'required|in:operations,dispatch,fleet_chauffeur,management,administration,maintenance',
                'designation'     => 'required|min:2|max:100',
                'employment_type' => 'required|in:full_time,part_time,contract,probation',
                'joining_date'    => 'required|date',
                'status'          => 'required|in:active,on_leave,terminated',
            ]);

            \$userId = !empty(\$input['user_id']) ? (int)\$input['user_id'] : null;

            \$this->employeeModel->update(\$id, [
                'user_id'           => \$userId,
                'first_name'        => trim(\$data['first_name']),
                'last_name'         => trim(\$data['last_name']),
                'phone'             => trim(\$data['phone']),
                'alternate_phone'   => !empty(\$input['alternate_phone']) ? trim(\$input['alternate_phone']) : null,
                'email'             => !empty(\$input['email']) ? strtolower(trim(\$input['email'])) : null,
                'department'        => \$data['department'],
                'designation'       => trim(\$data['designation']),
                'employment_type'   => \$data['employment_type'],
                'joining_date'      => \$data['joining_date'],
                'status'            => \$data['status'],
                'emergency_contact' => !empty(\$input['emergency_contact']) ? trim(\$input['emergency_contact']) : null,
                'address'           => !empty(\$input['address']) ? trim(\$input['address']) : null,
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);

            \$this->auditLog->record(
                'employee_updated',
                'employees',
                \$id,
                \$employee,
                \$data,
                Session::getUserId()
            );

            return \$this->redirect(url(\"/office/employees/{\$id}\"), 'success', \"Employee record [{\$employee['employee_code']}] successfully updated.\");

        } catch (ValidationException \$e) {
            Session::setFlash('form_errors', \$e->getErrors());
            Session::setFlash('danger', \$e->getMessage());
            return \$this->redirect(url(\"/office/employees/{\$id}/edit\"));
        } catch (Throwable \$e) {
            Session::setFlash('danger', 'Error updating employee: ' . \$e->getMessage());
            return \$this->redirect(url(\"/office/employees/{\$id}/edit\"));
        }
    }";

$replacement = '    public function edit(Request $request, array $params): Response
    {
        $id = (int)($params[\'id\'] ?? 0);
        $employee = $this->employeeModel->findWithDetails($id);
        if (!$employee) {
            return $this->redirect(url(\'/office/employees\'), \'danger\', \'Employee not found.\');
        }

        $roleModel = new \App\Models\Role();
        $roles = $roleModel->where("`status` = \'active\'", [], \'is_system DESC, name ASC\');

        $unlinkedUsers = $this->userModel->where("`role` IN (\'admin\', \'dispatcher\', \'driver\') AND `status` = \'active\'", [], \'name ASC\');

        return $this->render(\'office/employees/edit\', [
            \'employee\'      => $employee,
            \'roles\'         => $roles,
            \'unlinkedUsers\' => $unlinkedUsers,
            \'errors\'        => Session::getFlash(\'form_errors\') ?? [],
            \'old\'           => Session::getFlash(\'form_old\') ?? [],
            \'title\'         => "Edit Employee - {$employee[\'employee_code\']}",
        ], \'layouts/office\');
    }

    /**
     * Update existing employee record
     */
    public function update(Request $request, array $params): Response
    {
        $id = (int)($params[\'id\'] ?? 0);
        $employee = $this->employeeModel->findWithDetails($id);
        if (!$employee) {
            return $this->redirect(url(\'/office/employees\'), \'danger\', \'Employee not found.\');
        }

        try {
            $input = $request->all();

            $rules = [
                \'first_name\' => \'required|min:2|max:80\',
                \'last_name\'  => \'required|min:1|max:80\',
                \'phone\'      => \'required|min:8|max:30\',
                \'status\'     => \'required|in:active,on_leave,terminated\',
            ];
            if (!empty($input[\'email\'])) {
                $rules[\'email\'] = \'email|max:150\';
            }

            $this->validate($input, $rules);

            $userId = !empty($input[\'user_id\']) ? (int)$input[\'user_id\'] : ($employee[\'user_id\'] ?? null);

            // Department & Designation resolution
            $roleSlug = !empty($input[\'role\']) ? strtolower(trim((string)$input[\'role\'])) : null;
            if (empty($input[\'department\'])) {
                if ($roleSlug === \'driver\' || $roleSlug === \'chauffeur\') {
                    $department = \'fleet_chauffeur\';
                } elseif ($roleSlug === \'dispatcher\') {
                    $department = \'dispatch\';
                } elseif ($roleSlug === \'manager\') {
                    $department = \'management\';
                } elseif ($roleSlug === \'admin\') {
                    $department = \'administration\';
                } else {
                    $department = $employee[\'department\'] ?? \'operations\';
                }
            } else {
                $department = trim((string)$input[\'department\']);
            }

            if (empty($input[\'designation\'])) {
                if ($roleSlug === \'driver\' || $roleSlug === \'chauffeur\') {
                    $designation = \'Chauffeur / Driver\';
                } elseif ($roleSlug === \'dispatcher\') {
                    $designation = \'Fleet Dispatcher\';
                } elseif ($roleSlug === \'manager\') {
                    $designation = \'Operations Manager\';
                } elseif ($roleSlug === \'admin\') {
                    $designation = \'System Administrator\';
                } else {
                    $designation = $employee[\'designation\'] ?? \'Operations Executive\';
                }
            } else {
                $designation = trim((string)$input[\'designation\']);
            }

            // Address resolution
            $addressLine = !empty($input[\'address_line\']) ? trim((string)$input[\'address_line\']) : (!empty($input[\'address\']) ? trim((string)$input[\'address\']) : null);
            $city = !empty($input[\'city\']) ? trim((string)$input[\'city\']) : null;
            $state = !empty($input[\'state\']) ? trim((string)$input[\'state\']) : null;
            $postalCode = !empty($input[\'postal_code\']) ? trim((string)$input[\'postal_code\']) : null;
            $country = !empty($input[\'country\']) ? trim((string)$input[\'country\']) : \'India\';
            $fullAddress = trim(implode(\', \', array_filter([$addressLine, $city, $state, $postalCode, $country])));
            if (empty($fullAddress) && !empty($input[\'address\'])) {
                $fullAddress = trim((string)$input[\'address\']);
            }

            $updateData = [
                \'user_id\'                => $userId,
                \'first_name\'             => trim((string)$input[\'first_name\']),
                \'last_name\'              => trim((string)$input[\'last_name\']),
                \'phone\'                  => preg_replace(\'/[^\d+]/\', \'\', trim((string)$input[\'phone\'])),
                \'alternate_phone\'        => !empty($input[\'alternate_phone\']) ? preg_replace(\'/[^\d+]/\', \'\', trim((string)$input[\'alternate_phone\'])) : null,
                \'email\'                  => !empty($input[\'email\']) ? strtolower(trim((string)$input[\'email\'])) : null,
                \'date_of_birth\'          => !empty($input[\'date_of_birth\']) ? date(\'Y-m-d\', strtotime($input[\'date_of_birth\'])) : null,
                \'gender\'                 => !empty($input[\'gender\']) ? trim((string)$input[\'gender\']) : null,
                \'nationality\'            => !empty($input[\'nationality\']) ? trim((string)$input[\'nationality\']) : \'Indian\',
                \'department\'             => $department,
                \'designation\'            => $designation,
                \'employment_type\'        => !empty($input[\'employment_type\']) ? $input[\'employment_type\'] : ($employee[\'employment_type\'] ?? \'full_time\'),
                \'joining_date\'           => !empty($input[\'joining_date\']) ? date(\'Y-m-d\', strtotime($input[\'joining_date\'])) : ($employee[\'joining_date\'] ?? date(\'Y-m-d\')),
                \'status\'                 => !empty($input[\'status\']) ? $input[\'status\'] : ($employee[\'status\'] ?? \'active\'),
                \'emergency_contact\'      => !empty($input[\'emergency_contact\']) ? trim((string)$input[\'emergency_contact\']) : trim(($input[\'emergency_contact_name\'] ?? \'\') . \' \' . ($input[\'emergency_contact_phone\'] ?? \'\')),
                \'emergency_contact_name\' => !empty($input[\'emergency_contact_name\']) ? trim((string)$input[\'emergency_contact_name\']) : null,
                \'emergency_contact_phone\'=> !empty($input[\'emergency_contact_phone\']) ? trim((string)$input[\'emergency_contact_phone\']) : null,
                \'address\'                => $fullAddress ?: null,
                \'address_line\'           => $addressLine ?: null,
                \'city\'                   => $city ?: null,
                \'state\'                  => $state ?: null,
                \'postal_code\'            => $postalCode ?: null,
                \'country\'                => $country ?: \'India\',
                \'id_type\'                => !empty($input[\'id_type\']) ? trim((string)$input[\'id_type\']) : null,
                \'id_number\'              => !empty($input[\'id_number\']) ? trim((string)$input[\'id_number\']) : null,
                \'id_expiry\'              => !empty($input[\'id_expiry\']) ? date(\'Y-m-d\', strtotime($input[\'id_expiry\'])) : null,
                \'notes\'                  => !empty($input[\'notes\']) ? trim((string)$input[\'notes\']) : null,
                \'updated_at\'             => date(\'Y-m-d H:i:s\'),
            ];

            // 1. Profile photo upload or removal
            $uploadedAvatarPath = $this->saveUploadedFileOrBase64(
                $request,
                $input,
                \'profile_photo\',
                \'profile_photo_base64\',
                \'employees/avatars\',
                [
                    \'image/jpeg\'  => \'jpg\',
                    \'image/pjpeg\' => \'jpg\',
                    \'image/png\'   => \'png\',
                    \'image/x-png\' => \'png\',
                    \'image/webp\'  => \'webp\',
                ]
            );
            if ($uploadedAvatarPath) {
                if (!empty($employee[\'profile_photo\'])) {
                    $this->deletePhysicalFile($employee[\'profile_photo\']);
                }
                $updateData[\'profile_photo\'] = $uploadedAvatarPath;
            } elseif (!empty($input[\'remove_profile_photo\']) && $input[\'remove_profile_photo\'] === \'1\') {
                if (!empty($employee[\'profile_photo\'])) {
                    $this->deletePhysicalFile($employee[\'profile_photo\']);
                }
                $updateData[\'profile_photo\'] = null;
            }

            // 2. License document upload or removal
            $uploadedLicensePath = $this->saveUploadedFileOrBase64(
                $request,
                $input,
                \'license_document_file\',
                \'license_document_file_base64\',
                \'employees/licenses\',
                [
                    \'image/jpeg\'      => \'jpg\',
                    \'image/pjpeg\'     => \'jpg\',
                    \'image/png\'       => \'png\',
                    \'image/x-png\'     => \'png\',
                    \'image/webp\'      => \'webp\',
                    \'application/pdf\' => \'pdf\',
                ]
            );
            if ($uploadedLicensePath) {
                if (!empty($employee[\'license_document_file\'])) {
                    $this->deletePhysicalFile($employee[\'license_document_file\']);
                }
                $updateData[\'license_document_file\'] = $uploadedLicensePath;
            } elseif (!empty($input[\'remove_license_document\']) && $input[\'remove_license_document\'] === \'1\') {
                if (!empty($employee[\'license_document_file\'])) {
                    $this->deletePhysicalFile($employee[\'license_document_file\']);
                }
                $updateData[\'license_document_file\'] = null;
            }

            // 3. ID document upload or removal
            $uploadedDocPath = $this->saveUploadedFileOrBase64(
                $request,
                $input,
                \'id_document_file\',
                \'id_document_file_base64\',
                \'employees/documents\',
                [
                    \'image/jpeg\'      => \'jpg\',
                    \'image/pjpeg\'     => \'jpg\',
                    \'image/png\'       => \'png\',
                    \'image/x-png\'     => \'png\',
                    \'image/webp\'      => \'webp\',
                    \'application/pdf\' => \'pdf\',
                ]
            );
            if ($uploadedDocPath) {
                if (!empty($employee[\'id_document_file\'])) {
                    $this->deletePhysicalFile($employee[\'id_document_file\']);
                }
                $updateData[\'id_document_file\'] = $uploadedDocPath;
            } elseif (!empty($input[\'remove_id_document\']) && $input[\'remove_id_document\'] === \'1\') {
                if (!empty($employee[\'id_document_file\'])) {
                    $this->deletePhysicalFile($employee[\'id_document_file\']);
                }
                $updateData[\'id_document_file\'] = null;
            }

            // 4. Update primary employee record
            $this->employeeModel->update($id, $updateData);

            // 5. Driver Profile synchronization if license number or driver role is present
            $isDriverRole = in_array($department, [\'fleet_chauffeur\', \'driver\'], true) || !empty($input[\'license_number\']) || !empty($employee[\'driver_profile_id\']);
            if ($isDriverRole) {
                $driverModel = new \App\Models\DriverProfile();
                $driverProfile = $driverModel->findBy(\'employee_id\', $id);
                if (!$driverProfile && !empty($userId)) {
                    $driverProfile = $driverModel->findBy(\'user_id\', $userId);
                }

                $licenseNum = !empty($input[\'license_number\']) ? strtoupper(trim((string)$input[\'license_number\'])) : ($driverProfile[\'license_number\'] ?? null);
                $licenseExp = !empty($input[\'license_expiry\']) ? date(\'Y-m-d\', strtotime($input[\'license_expiry\'])) : ($driverProfile[\'license_expiry\'] ?? null);
                $expYears   = isset($input[\'experience_years\']) ? (int)$input[\'experience_years\'] : (isset($driverProfile[\'experience_years\']) ? (int)$driverProfile[\'experience_years\'] : 3);

                if ($driverProfile) {
                    $driverUpdate = [
                        \'employee_id\'      => $id,
                        \'experience_years\' => $expYears,
                    ];
                    if ($licenseNum) $driverUpdate[\'license_number\'] = $licenseNum;
                    if ($licenseExp) $driverUpdate[\'license_expiry\'] = $licenseExp;
                    if (isset($updateData[\'license_document_file\'])) {
                        $driverUpdate[\'license_document_file\'] = $updateData[\'license_document_file\'];
                    }
                    if ($userId && empty($driverProfile[\'user_id\'])) {
                        $driverUpdate[\'user_id\'] = $userId;
                    }
                    $driverModel->update((int)$driverProfile[\'id\'], $driverUpdate);
                } elseif (!empty($licenseNum)) {
                    $driverModel->create([
                        \'user_id\'               => $userId,
                        \'employee_id\'           => $id,
                        \'license_number\'        => $licenseNum,
                        \'license_expiry\'        => $licenseExp ?: date(\'Y-m-d\', strtotime(\'+3 years\')),
                        \'experience_years\'      => $expYears,
                        \'license_document_file\' => $updateData[\'license_document_file\'] ?? ($employee[\'license_document_file\'] ?? null),
                        \'duty_status\'           => \'off_duty\',
                        \'rating\'                => 5.00,
                    ]);
                }
            }

            $this->auditLog->record(
                \'employee_updated\',
                \'employees\',
                $id,
                $employee,
                $updateData,
                Session::getUserId()
            );

            return $this->redirect(url("/office/employees/{$id}"), \'success\', "Employee record [{$employee[\'employee_code\']}] successfully updated.");

        } catch (ValidationException $e) {
            Session::setFlash(\'form_errors\', $e->getErrors());
            Session::setFlash(\'form_old\', $request->all());
            Session::setFlash(\'danger\', $e->getMessage());
            return $this->redirect(url("/office/employees/{$id}/edit"));
        } catch (Throwable $e) {
            Session::setFlash(\'form_old\', $request->all());
            Session::setFlash(\'danger\', \'Error updating employee: \' . $e->getMessage());
            return $this->redirect(url("/office/employees/{$id}/edit"));
        }
    }';

$cNorm = str_replace("\r\n", "\n", $content);
$tNorm = str_replace("\r\n", "\n", $target);
$rNorm = str_replace("\r\n", "\n", $replacement);

if (strpos($cNorm, $tNorm) !== false) {
    $cNorm = str_replace($tNorm, $rNorm, $cNorm);
    if (strpos($content, "\r\n") !== false) {
        $cNorm = str_replace("\n", "\r\n", $cNorm);
    }
    file_put_contents($file, $cNorm);
    echo "SUCCESS: EmployeeController.php updated successfully!\n";
} else {
    echo "ERROR: Target not found in EmployeeController.php\n";
}

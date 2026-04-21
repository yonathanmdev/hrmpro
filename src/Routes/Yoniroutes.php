<?php
// src/Routes/Yoniroutes.php

return [
    // User Management
    'register-user'                 => ['UserController', 'showRegisterForm', true],
    'register-process'              => ['UserController', 'handleRegistration', true],
    'edit-user'                     => ['UserController', 'getUserById', true],
    'edit-user-process'             => ['UserController', 'handleUpdateUser', true],


    // Organization Management
    'register-organization'         => ['OrgController', 'showRegisterForm', true],
    'register-organization-process' => ['OrgController', 'handleRegistration', true],
    'update-organization-process'   => ['OrgController', 'handleEditOrganization', true],
    'register-branch'               => ['OrgController', 'showRegisterForm', true],
    'register-branch-process'       => ['OrgController', 'handleBranchRegistration', true],
    'register-director'          => ['DirectorController', 'showDirector', true],
    'register-director-process'  => ['DirectorController', 'handleDirector', true],
    'register-position'          => ['DirectorController', 'showPosition', true],
    'register-position-process'  => ['DirectorController', 'handlePositionRegistration', true],
    //employee
    'employee-registration'       => ['EmployeeRegistrationController', 'showForm', true],
    'employee-registration-save'  => ['EmployeeRegistrationController', 'handleRegistration', true],
    'employee-edit'               => ['EmployeeRegistrationController', 'showEditForm', true],
    'employee-edit-save'          => ['EmployeeRegistrationController', 'handleEdit', true],
    'onBoardingEmployees'          => ['EmployeeRegistrationController', 'onboardingEmployees', true],
    'employee-onboarding'          => ['EmployeeRegistrationController', 'listofOnboardingEmployees', true],
    'employee-onboarding-views'    => ['EmployeeRegistrationController', 'showOnBoardingForm', true],
    'employee-onboadring-approve'          => ['EmployeeRegistrationController', 'handleOnboardingApproval', true],
    'employee-views'               => ['EmployeeRegistrationController', 'employeeDetails', true],
    'employee-scholarship'         => ['ScholarshipController', 'showScholarshipForm', true],
    'employee-scholarship-search'  => ['ScholarshipController', 'liveSearch', true],
    'employee-scholarship-store'   => ['ScholarshipController', 'storeScholarship', true],
    'on-leave-scholarship-count'   => ['ScholarshipController', 'onLeaveScholarshipEmployees', true],
    'employee-scholarship-onleave' => ['ScholarshipController', 'showScholarshiponLeavePending', true],
    'employee-scholarship-onleave-views' => ['ScholarshipController', 'getScholarshipDetails', true],
    'employee-scholarship-onleave-approval' => ['ScholarshipController', 'handleOnLeaveApproval', true],
    // File Management
    'employee-archive' => ['ScholarshipController', 'getDocument', true],
    //debt suspension
    'employee-debt-suspension' => ['DebtSuspensionController', 'showDebtSuspensionForm', true],
    'employee-debt-search'  => ['DebtSuspensionController', 'liveSearch', true],
    'employee-debt-suspension-store'   => ['DebtSuspensionController', 'storeDebtSuspension', true],
    'debt-suspension-count'   => ['DebtSuspensionController', 'countPending', true],
    'employee-debt-suspension-pending' => ['DebtSuspensionController', 'showDebtSuspensionPending', true],
    'employee-debt-suspension-approval-view' => ['DebtSuspensionController', 'getDebtSuspensionDetails', true],
    'employee-debt-suspension-approval' => ['DebtSuspensionController', 'handleDebtSuspensionApproval', true],
    'employee-debt-suspension-clearing' => ['DebtSuspensionController', 'getDebtSuspensionClearing', true],
    'employee-debt-suspension-clearing-approval' => ['DebtSuspensionController', 'storeDebtSuspensionClearing', true],
    // Stored files 
    'serve-file' => ['FileController', 'serveFile', true], // true = auth required
    // Audit Logs
    'audit-logs'                   => ['AuditController', 'showAuditLogs', true],
    'audit-stats'                  => ['AuditController', 'getAuditStats', true],
];
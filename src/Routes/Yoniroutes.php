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
    
    // Stored files 
    'serve-file' => ['FileController', 'serveFile', true], // true = auth required
    // Audit Logs
    'audit-logs'                   => ['AuditController', 'showAuditLogs', true],
    'audit-stats'                  => ['AuditController', 'getAuditStats', true],
];
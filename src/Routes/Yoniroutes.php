<?php
// src/Routes/Yoniroutes.php

return [
    // User Management
    'register-user'                 => ['UserController', 'showRegisterForm', true],
    'register-process'              => ['UserController', 'handleRegistration', true],

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
];
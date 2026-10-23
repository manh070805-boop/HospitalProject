<?php

require_once __DIR__ . '/../app/core/PortalAccess.php';

$url = trim($_GET['url'] ?? '', '/');

if ($url === '') {
    if (PortalAccess::isAuthenticated()) {
        header('Location: ' . PortalAccess::getHomeUrlForRole(PortalAccess::currentRole()));
        exit;
    }

    $url = 'auth/login';
}

$segments = explode('/', $url);

$module = $segments[0] ?? 'auth';
$action = $segments[1] ?? '';



switch ($module) {
    case 'auth':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $controller = new AuthController();

        if ($action === '' || $action === 'login') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->login();
            } else {
                $controller->showLoginForm();
            }
        } elseif ($action === 'logout') {
            $controller->logout();
        } else {
            echo '404 - Route không tồn tại';
        }
        break;

    case 'dashboard':
        PortalAccess::requireAuth(['admin']);
        require_once __DIR__ . '/../app/controllers/Admin/DashboardController.php';
        $controller = new DashboardController();
        $controller->indexDashboard();
        break;

    case 'users':
        PortalAccess::requireAuth(['admin']);
        require_once __DIR__ . '/../app/controllers/Admin/UserController.php';
        $controller = new UserController();

        if ($action === '' || $action === 'index') {
            $controller->index();
        } elseif ($action === 'create') {
            $controller->showCreateForm();
        } elseif ($action === 'store') {
            $controller->storeUser();
        } elseif ($action === 'edit') {
            $controller->editUser();
        } elseif ($action === 'update') {
            $controller->updateUser();
        } elseif ($action === 'lock') {
            $controller->lockUser();
        } else {
            echo '404 - Route không tồn tại';
        }
        break;

    case 'departments':
        PortalAccess::requireAuth(['admin']);
        require_once __DIR__ . '/../app/controllers/Admin/DepartmentController.php';
        $controller = new DepartmentController();

        if ($action === '' || $action === 'index') {
            $controller->index();
        } elseif ($action === 'create') {
            $controller->showDepartment();
        } elseif ($action === 'store') {
            $controller->storeDepartment();
        } elseif ($action === 'edit') {
            $controller->editDepartment();
        } elseif ($action === 'update') {
            $controller->updateDepartment();
        } elseif ($action === 'delete') {
            $controller->deleteDepartment();
        } else {
            echo '404 - Route không tồn tại';
        }
        break;

    case 'roles':
        PortalAccess::requireAuth(['admin']);
        require_once __DIR__ . '/../app/controllers/Admin/RoleController.php';
        $controller = new RoleController();

        if ($action === '' || $action === 'index') {
            $controller->index();
        } elseif ($action === 'create') {
            $controller->showRole();
        } elseif ($action === 'store') {
            $controller->storeRole();
        } elseif ($action === 'edit') {
            $controller->editRole();
        } elseif ($action === 'update') {
            $controller->updateRole();
        } elseif ($action === 'delete') {
            $controller->deleteRole();
        } else {
            echo '404 - Route không tồn tại';
        }
        break;

    case 'patients':
        PortalAccess::requireAuth(['admin']);
        require_once __DIR__ . '/../app/controllers/Admin/PatientController.php';
        $controller = new PatientController();

        if ($action === '' || $action === 'index') {
            $controller->index();
        } elseif ($action === 'show') {
            $controller->viewPatient();
        } elseif ($action === 'edit') {
            $controller->editPatient();
        } elseif ($action === 'update') {
            $controller->updatePatient();
        } elseif ($action === 'delete') {
            $controller->deletePatient();
        } else {
            echo '404 - Route không tồn tại';
        }
        break;

    case 'system-logs':
        PortalAccess::requireAuth(['admin']);
        require_once __DIR__ . '/../app/controllers/Admin/SystemLogController.php';
        $controller = new SystemLogController();

        if ($action === '' || $action === 'index') {
            $controller->index();
        } elseif ($action === 'show') {
            $controller->show();
        } elseif ($action === 'stats') {
            $controller->stats();
        } else {
            echo '404 - Route không tồn tại';
        }
        break;

    case 'rooms':
        PortalAccess::requireAuth(['admin']);
        require_once __DIR__ . '/../app/controllers/Admin/RoomController.php';
        $controller = new RoomController();

        if ($action === '' || $action === 'index') {
            $controller->index();
        } elseif ($action === 'show') {
            $controller->show();
        } elseif ($action === 'create') {
            $controller->showCreateForm();
        } elseif ($action === 'store') {
            $controller->storeRoom();
        } elseif ($action === 'edit') {
            $controller->editRoom();
        } elseif ($action === 'update') {
            $controller->updateRoom();
        } elseif ($action === 'delete' || $action === 'destroy') {
            $controller->deleteRoom();
        } elseif ($action === 'create-bed') {
            $controller->showCreateBedForm();
        } elseif ($action === 'store-bed') {
            $controller->storeBed();
        } elseif ($action === 'edit-bed') {
            $controller->editBed();
        } elseif ($action === 'update-bed') {
            $controller->updateBed();
        } elseif ($action === 'delete-bed' || $action === 'destroy-bed') {
            $controller->deleteBed();
        } else {
            echo '404 - Route không tồn tại';
        }
        break;

    case 'internal':
        PortalAccess::requireAuth(['nurse', 'head_nurse']);

        $internalModule = $segments[1] ?? 'dashboard';
        $internalAction = $segments[2] ?? '';

        if ($internalModule === 'dashboard') {
            require_once __DIR__ . '/../app/controllers/Internal/DashboardController.php';
            $controller = new InternalDashboardController();
            $controller->index();
        } elseif ($internalModule === 'patients') {
            require_once __DIR__ . '/../app/controllers/Internal/PatientIntakeController.php';
            $controller = new PatientIntakeController();

            if ($internalAction === '' || $internalAction === 'index' || $internalAction === 'search') {
                $controller->index();
            } elseif ($internalAction === 'check-cccd') {
                $controller->checkCccd();
            } elseif ($internalAction === 'create') {
                $controller->showCreateForm();
            } elseif ($internalAction === 'store') {
                $controller->storePatient();
            } elseif ($internalAction === 'show') {
                $controller->showPatient();
            } else {
                echo '404 - Route không tồn tại';
            }
        } elseif ($internalModule === 'admissions') {
            require_once __DIR__ . '/../app/controllers/Internal/InternalAdmissionController.php';
            $controller = new InternalAdmissionController();

            if ($internalAction === 'create') {
                $controller->showCreateForm();
            } elseif ($internalAction === 'store') {
                $controller->storeAdmission();
            } elseif ($internalAction === 'show') {
                $controller->showDetail();
            } elseif ($internalAction === 'transfer') {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->transferAdmission();
                } else {
                    $controller->showTransferForm();
                }
            } elseif ($internalAction === 'status') {
                $controller->updateStatus();
            } elseif ($internalAction === 'bed-history') {
                $controller->bedHistory();
            } else {
                echo '404 - Route không tồn tại';
            }
        } elseif ($internalModule === 'monitoring') {
            require_once __DIR__ . '/../app/controllers/Internal/MonitoringController.php';
            $controller = new MonitoringController();

            if ($internalAction === '' || $internalAction === 'index') {
                $controller->index();
            } else {
                echo '404 - Route không tồn tại';
            }
        } elseif ($internalModule === 'vitals') {
            require_once __DIR__ . '/../app/controllers/Internal/VitalSignController.php';
            $controller = new VitalSignController();

            if ($internalAction === '' || $internalAction === 'index') {
                $controller->index();
            } elseif ($internalAction === 'create') {
                $controller->showCreateForm();
            } elseif ($internalAction === 'store') {
                $controller->store();
            } elseif ($internalAction === 'history') {
                $controller->history();
            } else {
                echo '404 - Route không tồn tại';
            }
        } elseif ($internalModule === 'alerts') {
            require_once __DIR__ . '/../app/controllers/Internal/AlertController.php';
            $controller = new AlertController();

            if ($internalAction === '' || $internalAction === 'index') {
                $controller->index();
            } elseif ($internalAction === 'acknowledge') {
                $controller->acknowledge();
            } elseif ($internalAction === 'resolve') {
                $controller->resolve();
            } else {
                echo '404 - Route không tồn tại';
            }
        } elseif ($internalModule === 'treatment-plans') {
            require_once __DIR__ . '/../app/controllers/Internal/TreatmentPlanController.php';
            $controller = new TreatmentPlanController();

            if ($internalAction === 'show') {
                $controller->show();
            } elseif ($internalAction === 'history') {
                $controller->history();
            } else {
                echo '404 - Route không tồn tại';
            }
        } elseif ($internalModule === 'care-plans') {
            require_once __DIR__ . '/../app/controllers/Internal/CarePlanController.php';
            $controller = new CarePlanController();

            if ($internalAction === '' || $internalAction === 'index') {
                $controller->index();
            } elseif ($internalAction === 'create') {
                $controller->showCreateForm();
            } elseif ($internalAction === 'store') {
                $controller->store();
            } elseif ($internalAction === 'show') {
                $controller->show();
            } elseif ($internalAction === 'edit') {
                $controller->showEditForm();
            } elseif ($internalAction === 'update') {
                $controller->update();
            } elseif ($internalAction === 'submit') {
                $controller->submit();
            } elseif ($internalAction === 'history') {
                $controller->history();
            } elseif ($internalAction === 'approval-history') {
                $controller->approvalHistory();
            } elseif ($internalAction === 'delete') {
                $controller->delete();
            } else {
                echo '404 - Route không tồn tại';
            }
        } elseif ($internalModule === 'approvals') {
            require_once __DIR__ . '/../app/controllers/Internal/ApprovalController.php';
            $controller = new ApprovalController();

            if ($internalAction === '' || $internalAction === 'index') {
                $controller->index();
            } elseif ($internalAction === 'show') {
                $controller->show();
            } elseif ($internalAction === 'approve') {
                $controller->approve();
            } elseif ($internalAction === 'reject') {
                $controller->reject();
            } else {
                echo '404 - Route không tồn tại';
            }
        } elseif ($internalModule === 'tasks') {
            require_once __DIR__ . '/../app/controllers/Internal/ShiftTaskController.php';
            $controller = new ShiftTaskController();

            if ($internalAction === '' || $internalAction === 'index') {
                $controller->index();
            } elseif ($internalAction === 'assign') {
                $controller->assign();
            } elseif ($internalAction === 'start') {
                $controller->start();
            } elseif ($internalAction === 'complete') {
                $controller->complete();
            } elseif ($internalAction === 'skip') {
                $controller->skip();
            } else {
                echo '404 - Route không tồn tại';
            }
        } else {
            echo '404 - Route không tồn tại';
        }
        break;


    case 'doctor-portal':
        PortalAccess::requireAuth(['doctor']);

        $doctorModule = $segments[1] ?? 'dashboard';
        $doctorAction = $segments[2] ?? '';

        if ($doctorModule === '' || $doctorModule === 'dashboard') {
            require_once __DIR__ . '/../app/controllers/Doctor/DoctorPortalController.php';
            $controller = new DoctorPortalController();
            $controller->index();
        } elseif ($doctorModule === 'patients' || $doctorModule === 'admissions') {
            require_once __DIR__ . '/../app/controllers/Doctor/DoctorAdmissionController.php';
            $controller = new DoctorAdmissionController();

            if ($doctorAction === 'show') {
                $controller->show();
            } else {
                $controller->index();
            }
        } elseif ($doctorModule === 'alerts') {
            require_once __DIR__ . '/../app/controllers/Doctor/DoctorAlertController.php';
            $controller = new DoctorAlertController();
            $controller->index();
        } elseif ($doctorModule === 'care-plans') {
            require_once __DIR__ . '/../app/controllers/Doctor/DoctorCarePlanController.php';
            $controller = new DoctorCarePlanController();

            if ($doctorAction === 'show') {
                $controller->show();
            } else {
                $controller->index();
            }
        } elseif ($doctorModule === 'treatment-plans') {
            require_once __DIR__ . '/../app/controllers/Doctor/DoctorTreatmentPlanController.php';
            $controller = new DoctorTreatmentPlanController();

            if ($doctorAction === '' || $doctorAction === 'index') {
                $controller->index();
            } elseif ($doctorAction === 'create') {
                $controller->showCreateForm();
            } elseif ($doctorAction === 'store') {
                $controller->store();
            } elseif ($doctorAction === 'show') {
                $controller->show();
            } elseif ($doctorAction === 'history') {
                $controller->history();
            } elseif ($doctorAction === 'stop') {
                $controller->stop();
            } elseif ($doctorAction === 'complete') {
                $controller->complete();
            } else {
                echo '404 - Route không tồn tại';
            }

        } else {
            echo '404 - Route không tồn tại';
        }
        break;

}


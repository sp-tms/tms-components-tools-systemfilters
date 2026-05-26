<?php

namespace Apps\Tms\Components\System\Filters;

use Apps\Fintech\Packages\Adminltetags\Traits\DynamicTable;
use System\Base\BaseComponent;

class FiltersComponent extends BaseComponent
{
    use DynamicTable;

    protected $filters;

    public function initialize()
    {
        $this->filters = $this->basepackages->filters;
    }

    /**
     * @acl(name=view)
     */
    public function viewAction()
    {
        if ($this->request->isGet()) {
            if ($this->app['id'] == 1) {
                $components = $this->modules->components->components;
            } else {
                $components = $this->modules->components->getComponentsForAppType($this->app['app_type']);
            }

            foreach ($components as $key => $component) {
                $components[$key]['name'] = $component['name'] . ' (' . $component['category'] . ')';
            }

            $this->view->components = $components;
        }

        if (isset($this->getData()['id'])) {
            if ($this->getData()['id'] != 0) {
                $filter = $this->filters->getById($this->getData()['id']);

                if (!$filter) {
                    return $this->throwIdNotFound();
                }

                $this->view->filter = $filter;
            }

            $this->view->pick('filters/view');

            return;
        }

        $conditions =
            [
                'conditions'    => '-|app_type|equals|' . $this->app['app_type'] . '&'
            ];

        if ($this->request->isPost()) {
            $replaceColumns =
                [
                    'filter_type' => ['html'  =>
                        [
                            '0' =>  'System',
                            '1' =>  'User'
                        ]
                    ],
                    'is_default' => ['html'  =>
                        [
                            '0' =>  'No',
                            '1' =>  'Yes'
                        ]
                    ],
                    'auto_generated' => ['html'  =>
                        [
                            '0' =>  'No',
                            '1' =>  'Yes'
                        ]
                    ]
                ];
        } else {
            $replaceColumns = null;
        }

        $controlActions =
            [
                'actionsToEnable'       =>
                [
                    'edit'      => 'system/filters',
                    'remove'    => 'system/filters/remove'
                ]
            ];

        $this->generateDTContent(
            $this->filters,
            'system/filters/view',
            $conditions,
            ['name', 'app_type', 'filter_type', 'auto_generated', 'is_default'],
            true,
            ['name', 'app_type', 'filter_type', 'auto_generated', 'is_default'],
            $controlActions,
            null,
            $replaceColumns,
            'name'
        );

        $this->view->pick('filters/list');
    }

    /**
     * @acl(name=add)
     */
    public function addAction()
    {
        $this->requestIsPost();

        if ($this->app['id'] === 1) {
            $this->filters->addFilter($this->postData());

            $this->view->filters = $this->filters->packagesData->filters;
        } else {
            //Adding clone in add as cloning requires add permission so both add and clone can be performed in same action.
            if (isset($this->postData()['clone']) && $this->postData()['clone']) {
                $this->filters->cloneFilter($this->postData());
            } else {
                $this->filters->addFilter($this->postData());
            }

            if (isset($this->postData()['component_id'])) {
                $this->view->filters = $this->filters->packagesData->filters;
            }
        }

        $this->addResponse(
            $this->filters->packagesData->responseMessage,
            $this->filters->packagesData->responseCode
        );
    }

    /**
     * @acl(name=update)
     */
    public function updateAction()
    {
        $this->requestIsPost();

        $this->filters->updateFilter($this->postData());

        if ($this->app['id'] == 1) {
            $this->view->filters = $this->filters->packagesData->filters;
        } else {
            if (isset($this->postData()['component_id'])) {
                $this->view->filters = $this->filters->packagesData->filters;
            }
        }

        $this->addResponse(
            $this->filters->packagesData->responseMessage,
            $this->filters->packagesData->responseCode
        );
    }

    protected function cloneAction()
    {
        $this->requestIsPost();

        $this->filters->cloneFilter($this->postData());

        $this->view->filters = $this->filters->packagesData->filters;

        $this->addResponse(
            $this->filters->packagesData->responseMessage,
            $this->filters->packagesData->responseCode
        );
    }

    /**
     * @acl(name=remove)
     */
    public function removeAction()
    {
        $this->requestIsPost();

        $removeFilter = $this->filters->removeFilter($this->postData());

        $this->addResponse(
            $this->filters->packagesData->responseMessage,
            $this->filters->packagesData->responseCode
        );

        if ($removeFilter) {
            if ($this->app['id'] === 1) {
                $this->view->filters = $this->filters->packagesData->filters;
            } else {
                if (isset($this->postData()['component_id'])) {
                    $this->view->filters = $this->filters->packagesData->filters;
                }
            }
        }
    }

    public function getdefaultfilterAction()
    {
        $this->requestIsPost();

        if ($this->filters->getDefaultFilter($this->postData()['component_id'])) {
            $this->view->defaultFilter = $this->filters->packagesData->defaultFilter;
        }

        $this->addResponse(
            $this->filters->packagesData->responseMessage,
            $this->filters->packagesData->responseCode
        );
    }
}

<?php

namespace Apps\Tms\Components\System\Filters\Install;

use System\Base\BasePackage;

class Install extends BasePackage
{
    public function init()
    {
        return $this;
    }

    public function install()
    {
        return true;
    }
}

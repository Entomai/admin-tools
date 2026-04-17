<?php

namespace Botble\AdminTools;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('admin_tools_translations');
        Schema::dropIfExists('admin_tools');
    }
}

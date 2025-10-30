<?php declare(strict_types=1);

namespace Ibr\IbrVacationPlugin;

use Shopware\Core\Framework\Plugin;

class IbrVacationPlugin extends Plugin {
    public function executeComposerCommands(): bool
    {
        return true;
    }
}

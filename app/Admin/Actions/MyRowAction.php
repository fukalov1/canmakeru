<?php

namespace App\Admin\Actions;

use Encore\Admin\Actions\RowAction;

class MyRowAction extends RowAction
{

    public function render()
    {
        if ($href = $this->href()) {
            return "<a href='{$href}' target='_blank'>{$this->name()}</a>";
        }

        $this->addScript();

        $attributes = $this->formatAttributes();

        return sprintf(
            "<a data-_key='%s' href='javascript:void(0);' class='%s' {$attributes}>%s</a>",
            $this->getKey(),
            $this->getElementClass(),
            $this->asColumn ? $this->display($this->row($this->column->getName())) : $this->name()
        );
    }


}

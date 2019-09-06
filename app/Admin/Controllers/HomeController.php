<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
//use Encore\Admin\Controllers\Dashboard;
use App\Admin\Controllers\MyDashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->title('Dashboard')
            ->description('Description...')
            ->row(MyDashboard::title())
            ->row(function (Row $row) {

                $row->column(4, function (Column $column) {
                    $column->append(MyDashboard::info());
                });

                $row->column(4, function (Column $column) {
                    $column->append(MyDashboard::refreshToken());
                });

                $row->column(4, function (Column $column) {
                    $column->append(MyDashboard::dependencies());
                });
            });
    }
}

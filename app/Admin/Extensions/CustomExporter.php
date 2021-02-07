<?php

namespace App\Admin\Extensions;
use Encore\Admin\Grid;
use Illuminate\Support\Arr;
use Encore\Admin\Grid\Exporters\AbstractExporter;

class CustomExporter extends AbstractExporter
{
    protected $filename = 'table';
    protected $head = [];
    protected $body = [];

    public function __construct($filename = 'table', $head = null, $body = null, Grid $grid = null)
    {
        $this->filename = $filename;
        $this->head = $head;
        $this->body = $body;
        parent::__construct($grid);
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $titles = [];
        $filename = $this->filename.'.csv';
        $data = $this->getData();
        if (!empty($data)) {
            $columns = array_dot($this->sanitize($data[0]));
            $titles = array_keys($columns);
        }
        $output = self::putcsv(($this->head == []) ? array_keys($columns) : $this->head);
        if($this->body == []) {
            foreach ($data as $row) {
                $row = array_only($row, $titles);
                $output .= self::putcsv(array_dot($row));
            }
        }else {
            foreach ($this->body as $row) {
                $output .= self::putcsv(array_dot($row));
            }
        }
        $headers = [
            'Content-Encoding'    => 'UTF-8',
            'Content-Type'        => 'text/csv;charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        response(rtrim($output, "\n"), 200, $headers)->send();
        exit;
    }

    /**
     * Remove indexed array.
     *
     * @param array $row
     *
     * @return array
     */
    protected function sanitize(array $row)
    {
        return collect($row)->reject(function ($val) {
            return is_array($val) && !Arr::isAssoc($val);
        })->toArray();
    }

    /**
     * @param $row
     * @param string $fd
     * @param string $quot
     *
     * @return string
     */
    protected static function putcsv($row, $fd = ',', $quot = '"')
    {
        $str = '';
        foreach ($row as $cell) {
            $cell = str_replace([$quot, "\n"], [$quot.$quot, ''], $cell);
            if (strstr($cell, $fd) !== false || strstr($cell, $quot) !== false) {
                $str .= $quot.$cell.$quot.$fd;
            } else {
                $str .= $cell.$fd;
            }
        }
        return substr($str, 0, -1)."\n";
    }
}

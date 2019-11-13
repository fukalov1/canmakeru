<?php

namespace App;

use App\Protokol;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
//use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use App\SlaveCustomer;

class Customer extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $guard = 'customer';

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dates = ['deleted_at'];

    public function slave_customers() {
        return $this->hasMany(SlaveCustomer::class);
    }

    public function getWorkers() {
        return $this->whereIn('id', $this->find(auth()->guard('customer')->user()->id)->slave_customers->pluck('slave_id'))->get();
    }

    public function getProtokols($customer_id=0)
    {
        $data = [];
        if ($customer_id!=0) {
            $data =  $this->find($customer_id)->protokols;
        }
        return json_encode(['data' => $data, 'customer_id' => $customer_id]);
    }

    public function protokols() {
	    return $this->hasMany(Protokol::class);
    }

    public function getDataChart($id) {
//        $id = auth()->guard('customer')->user()->id;
        $quest  = Customer::join('protokols','customers.id','protokols.customer_id')
            ->select(\DB::raw('date_format(protokols.protokol_dt, "%Y-%m") as date, count(protokols.protokol_num) count'))
            ->whereRaw('date_format(protokol_dt, "%Y-%m") <> "0000-00"')
            ->where('customer_id', $id)
            ->groupBy(\DB::raw('date_format(protokol_dt, "%Y-%m")'))
            ->get();

        $labels = [];
        $data = [];
        foreach ($quest as $item) {
            $labels[] = $item->date;
            $data[] = $item->count;
        }
//
//        return json_encode($quest);

        return [
            'labels' => $labels,
            'datasets' => array([
                'label' => 'Поверки за период (кол-во по месяцам)',
                'backgroundColor' => '#3490dc',
                'data' => $data,
            ])
        ];

    }

    public function getDataReportDays($id,$start,$end)
    {
        $labels = [];
        $data = [];

        if ($start and $end) {

            $quest = Customer::join('protokols', 'customers.id', 'protokols.customer_id')
                ->select(\DB::raw('date_format(protokols.protokol_dt, "%Y-%m-%d") as date, count(protokols.protokol_num) count'))
                ->where('protokol_dt', '>=', $start)
                ->where('protokol_dt', '<=', $end)
                ->where('customer_id', $id)
                ->groupBy(\DB::raw('date_format(protokol_dt, "%Y-%m-%d")'))
                ->get();

            foreach ($quest as $item) {
                $labels[] = $item->date;
                $data[] = $item->count;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => array([
                'label' => 'Поверки за период (кол-во по дням)',
                'backgroundColor' => '#3490dc',
                'data' => $data,
            ])
        ];

    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

}

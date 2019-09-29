<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckRole;
use App\VpnUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class VpnController extends Controller
{
    public function __construct()
    {
        $this->middleware(CheckRole::class);
    }
    public function index(){
        $vpns = VpnUser::all();
        return view('vpnall',['vpn'=>$vpns]);
    }
    public function save(Request $r){
        echo $r;
        $this->saveconf(VpnUser::all());

    }
    public function edit(Request $r, $id = ""){
        if ($r->method() == 'POST'){
            if(!$r->fname){
                $fname = 'Свободно';
            }
            else {
                $fname = $r->fname;
            }
            $vpns = VpnUser::all();

            if($r->id){
                $vpn = VpnUser::find($r->id);
                $vpn->username = $r->login;
                $vpn->password = $r->pwd;
                $vpn->fullname = $fname;
                $vpn->ip = $r->ip;
                $vpn->comment = $r->comment;
                $vpn->server = "*";
                $vpn->save();
            }
            else {
                if($vpns->where('username','=',$r->login)){
                    return "Cant create vpn user, login already exists";

                }
                if($vpns->where('ip','=',$r->ip)){
                    return "Cant create vpn user, ip already exists";
                }
                $vpn = new VpnUser();
                $vpn->username = $r->login;
                $vpn->password = $r->pwd;
                $vpn->fullname = $fname;
                $vpn->ip = $r->ip;
                $vpn->comment = $r->comment;
                $vpn->server = "*";
                $vpn->save();
            }
            $vpns = VpnUser::all();
            $this->saveconf($vpns);
            return redirect()->action('VpnController@index');
        }
        if(!$id){
            return view('editvpn');
        }
        $vpn = VpnUser::find($id);
        return view('editvpn',['vpn'=>$vpn]);

    }
    private function saveconf(Collection $vpns){
        $fh = fopen('../chap_secrets.conf','w');
        foreach ($vpns as $vpn) {
            $str = "$vpn->username $vpn->server $vpn->password $vpn->ip \n";
            fputs($fh,$str);

        }
        fclose($fh);
        exec('sudo /bin/systemctl restart pptpd');
        exec('sudo /bin/systemctl restart x2ltpd');
    }
}

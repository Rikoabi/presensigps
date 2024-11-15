<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class PresensiController extends Controller
{
    //
    public function create()
    {
        $hariini = date("Y-m-d");
        $email = Auth::guard('karyawan')->user()->email;
        $cek = DB::table('presensi')->where('tgl_presensi', $hariini)->where('email', $email)->count();
        return view('presensi.create', compact('cek'));
    }

    public function store(Request $request)
    {
        $email = Auth::guard('karyawan')->user()->email;
        $tgl_presensi = date("Y-m-d");
        $jam = date("H:i:s");

        $latitudekantor = -6.237855;
        $longitudekantor = 106.751304;
        $lokasi = $request->lokasi;
        // dd($lokasi);
        $lokasiuser = explode(",", $lokasi);
        $latitudeuser = $lokasiuser[0];
        $longitudeuser = $lokasiuser[1];
        $jarak = $this->distance($latitudekantor, $longitudekantor, $latitudeuser, $longitudeuser);
        $radius = round($jarak["meters"]);

        $cek = DB::table('presensi')->where('tgl_presensi', $tgl_presensi)->where('email', $email)->count();
        if ($cek > 0) {
            $ket = "out";
        } else {
            $ket = "in";
        }

        $image = $request->image;
        $folderPath = "public/uploads/absensi/";
        $formatName = $email . "-" . $tgl_presensi . "-" . $ket;
        $image_parts = explode(";base64", $image);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName  = $formatName . ".png";
        $file = $folderPath . $fileName;

        if ($radius > 40) {
            echo "error|Maaf Anda Berada diluar Radius, Jarak anda " . $radius . " meter dari kantor|radius";
        } else {
            if ($cek > 0) {
                $data_pulang = [
                    'jam_out' => $jam,
                    'foto_out' => $fileName,
                    'lokasi_out' => $lokasi
                ];
                $update = DB::table('presensi')->where('tgl_presensi', $tgl_presensi)->where('email', $email)->update($data_pulang);
                if ($update) {
                    echo "success|Terima Kasih, Hati-Hati Di Jalan|out";
                    Storage::put($file, $image_base64);
                } else {
                    echo "error|Maaf Gagal Absen|out";
                }
            } else {
                $data = [
                    'email' => $email,
                    'tgl_presensi' => $tgl_presensi,
                    'jam_in' => $jam,
                    'foto_in' => $fileName,
                    'lokasi_in' => $lokasi
                ];
                $simpan = DB::table('presensi')->insert($data);
                if ($simpan) {
                    echo "success|Terima Kasih, Selamat Bekerja|in";
                    Storage::put($file, $image_base64);
                } else {
                    echo "error|Maaf Gagal Absen|in";
                }
            }
        }
    }
    //Menghitung Jarak
    function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        return compact('meters');
    }

    public function editprofile()
    {
        $email = Auth::guard('karyawan')->user()->email;
        $karyawan = DB::table('karyawan')->where('email', $email)->first();
        return view('presensi.editprofile', compact('karyawan'));
    }

    public function updateprofile(Request $request)
    {
        $email = Auth::guard('karyawan')->user()->email;
        $nama_lengkap = $request->nama_lengkap;
        $no_hp = $request->no_hp;
        $password = Hash::make($request->password);

        if (!empty($password)) {
            # code...
            $data = [
                'nama_lengkap' => $nama_lengkap,
                'no_hp' => $no_hp,
                'password' => $password,
            ];
        } else {
            # code...
            $data = [
                'nama_lengkap' => $nama_lengkap,
                'no_hp' => $no_hp,
                
            ];
        }
        $update = DB::table('karyawan')->where('email',$email)->update($data);
        if($update){
            return Redirect::back()->with(['success' => 'Data berhasil di update']);
        }else{
            return Redirect::back()>with(['error' => 'Data Gagal']);;
        }
    }
}

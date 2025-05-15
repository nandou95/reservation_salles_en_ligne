<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Language extends BaseController
{
    public function index()
    {
        //https://stackoverflow.com/questions/60250996/how-to-set-specific-language-for-all-pages-in-codeigniter-4
        //https://onlinewebtutorblog.com/how-to-create-codeigniter-4-multiple-language-website/

        $session = session();
        $locale = $this->request->getLocale();
        $session->remove('lang');
        $session->set('lang', $locale);
        $url = base_url();
       // return redirect()->to($url);
        return redirect()->back();
    }
}

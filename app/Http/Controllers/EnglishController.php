<?php

namespace App\Http\Controllers;

use App\Models\English;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreEnglishRequest;
use App\Http\Requests\UpdateEnglishRequest;
use Illuminate\Http\Request;
use Path\To\DOMDocument;
use Intervention\Image\ImageManagerStatic as Image;

class EnglishController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user(); // Mengambil pengguna saat ini

        $requestedSlugs = [];

        if (Gate::allows('admin')) {
            $requestedSlugs = ['COMPANYPROFILE', 'COMPANYBOARDOFDIRECTORS',
            'COMPANYVISION,MISSION&VALUES','COMPANYHIGHLIGHTS&ACHIEVEMENTSOVERVIEW'
            ,'COMPANYKANCERTIFICATION','COMPANYJISCERTIFICATION','COMPANYSNICERTIFICATION',
            'COMPANYSIRIMCERTIFICATION','COMPANYISOCERTIFICATION','COMPANYTKDNCERTIFICATION',
            'COMPANYGROUPVIDEO','COMPANYMANAGEMENTSYSTEM','INDUSTRIALPROCESSFACILITAS',
            'INDUSTRIALPROCESSFLOWCHARTOFSTEELMAKING','INDUSTRIALPROCESSFLOWCHARTOFWIRERODROLING',
            'INDUSTRIALPROCESSISPATPANCAPUTRAFACILITAS','INDUSTRIALPROCESSISPATBUKITBAJAFACILITAS',
            'INDUSTRIALPROCESSISPATWIREPRODUCTSFACILITAS','SUBSIDIARIESPT.ISPATWIREPRODUCT',
            'SUBSIDIARIESPT.ISPATPANCAPUTRA','SUBSIDIARIESPT.ISPATBUKITBAJA',
            'BROCHUREPT.ISPATINDO','BROCHUREPT.ISPATWIREPRODUCT','BROCHUREPT.ISPATPANCAPUTRA',
            'BROCHUREPT.ISPATBUKITBAJA'];
        } elseif (Gate::allows('she')) {
            $requestedSlugs = ['ENVIRONMENT', 'COMPANYSHE'];
        } elseif (Gate::allows('personalia')) {
            $requestedSlugs = ['CAREERS'];
        } elseif (Gate::allows('qualitycontrol')) {
            $requestedSlugs = ['PRODUCTHIGHCARBONSTEEL', 'PRODUCTLOWCARBONSTEEL','PRODUCTCOLDHEADINGQUALITYSTEEL',
            'PRODUCTGENERALPURPOSEWR','PRODUCTWELDINGELECTRODE','PRODUCTPLAINDEFORMBAR','PRODUCTGENERALSTRUCTURE',
            'PRODUCTNAILS&NAILWIRE','PRODUCTSCRAPPROVIDER'];
        } else {
            return redirect('/admin/news');
        }

        // Mengambil data English yang sesuai dengan slug dan name pengguna saat ini
        $posts = English::whereIn('slug', $requestedSlugs)
            ->latest()
            ->paginate(7);

        $english = English::all();

        return view('admin.layout.english', [
            'title' => 'My Posts In English',
            'posts' => $posts,
            'user' => $user->name,
        ]);
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Return view('admin.layout.createenglish',[
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules=[
            'title' => ['required'],
            'slug' => ['required','unique:englishes'],
            'content' => ['required']
        ];

        $this->validate($request,$rules);

        $storage="file/content";
        $dom=new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($request->content,LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NOIMPLIED);
        libxml_clear_errors();
        $images=$dom->getElementsByTagName('img');
        foreach($images as $img){
            $src=$img->getAttribute('src');
            if(preg_match('/data:image/',$src)){
                preg_match('/data:image\/(?<mime>.*?)\;/',$src,$groups);
                $mimetype=$groups['mime'];
                $fileNameContent = uniqid();
                $fileNameContentRand=substr(md5($fileNameContent),6,6).'_'.time();
                $filepath=("$storage/$fileNameContentRand.$mimetype");
                $image = Image::make($src)
                ->encode($mimetype,100)
                ->save(public_path($filepath));
                $new_src=asset($filepath);
                $img->removeAttribute('src');
                $img->setAttribute('src',$new_src);
                $img->setAttribute('class','img-responsive');
        }

    }

    $article = English::create([
        'title' => $request->title,
        'name' => auth()->user()->name,
        'slug' => $request->slug,
        'content' => $dom->saveHTML()

    ]);


     return redirect('/admin/english');
    }

    /**
     * Display the specified resource.
     */
    public function show(English $english)
    {
        return View ('admin.layout.englishshow',[
            'english' => $english
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(English $english)
    {
        Return view('admin.layout.englishedit',[
            'english' => $english,
            'content' => $english->content,
            'slug' => $english->slug
         ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, English $english)
    {
        $rules=[
            'title' => ['required'],
            'slug' => ['required',],
            'content' => ['required']
        ];

        $this->validate($request,$rules);

        $storage="file/content";
        $dom=new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($request->content,LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NOIMPLIED);
        libxml_clear_errors();
        $images=$dom->getElementsByTagName('img');
        foreach($images as $img){
            $src=$img->getAttribute('src');
            if(preg_match('/data:image/',$src)){
                preg_match('/data:image\/(?<mime>.*?)\;/',$src,$groups);
                $mimetype=$groups['mime'];
                $fileNameContent = uniqid();
                $fileNameContentRand=substr(md5($fileNameContent),6,6).'_'.time();
                $filepath=("$storage/$fileNameContentRand.$mimetype");
                $image = Image::make($src)
                ->encode($mimetype,100)
                ->save(public_path($filepath));
                $new_src=asset($filepath);
                $img->removeAttribute('src');
                $img->setAttribute('src',$new_src);
                $img->setAttribute('class','img-responsive');
        }

    }


    English::where('id',$english->id)->update([
        'title' => $request->title,
        'name' => auth()->user()->name,
        'slug' => $request->slug,
        'content' => $dom->saveHTML()

    ]);

    return
    redirect('/admin/english');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(English $english)
    {
        // Mengasumsikan Anda memiliki 'id' sebagai parameter rute
        $id = $english->id;
        $content = $english->content;

        $this->deleteImageByContent($content, $id);

        English::destroy($id);

        return redirect('/admin/english')->with('success', 'Post has been deleted');
    }
// ...

    public function removeImage(Request $request)
    {
        try {
            $content = $request->content;
            $id = $request->id;
            $imageUrl = $request->input('imageUrl');

            // Debugging: Cetak nilai $content dan $id untuk memeriksa
            // dd($content, $id);

            // Lakukan logika penghapusan gambar berdasarkan URL
            $this->deleteImageByURL($imageUrl, $content, $id);

            return response()->json(['message' => 'Image removed successfully']);
        } catch (\Exception $e) {
            // Cetak pesan kesalahan untuk debugging
            dd($e->getMessage());
        }
    }


// ...





    protected function deleteImageByURL($imageUrl, $content, $id)
    {

               // Debugging: Cetak nilai $imageUrl untuk memeriksa
               dd($imageUrl);
               // Debugging: Cetak nilai $content dan $id untuk memeriksa
               dd($content, $id);
               // Debugging: Cetak pesan atau nilai untuk memeriksa
               dd("Trying to delete image:", $imageUrl, "Content:", $content, "ID:", $id);

        $dom = new \DOMDocument;
        $dom->loadHTML(html_entity_decode($content));
        $dom->preserveWhiteSpace = false;
        $imgs = $dom->getElementsByTagName("img");
        $links = [];
        dd($dom);

        // Hanya tambahkan URL gambar yang sesuai dengan URL yang dikirim dari client
        for ($i = 0; $i < $imgs->length; $i++) {
            $imgURL = $imgs->item($i)->getAttribute("src");
            if ($imgURL === $imageUrl) {
                $links[] = $imgURL;
            }
        }




        // Mengasumsikan gambar disimpan dalam folder 'content' dengan ID pos sebagai subfolder
        $path = public_path("file/content/{$id}/");

        $files = scandir($path);
        $result = array_intersect($files, $links);

        foreach ($result as $deleteFile) {
            $deletePath = $path . $deleteFile;
            if (file_exists($deletePath)) {
                unlink($deletePath);
            }
        }
    }



}

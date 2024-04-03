<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contacts;

class Tags extends Model
{
    use HasFactory;

    protected $fillable = ['tag','color','id'];
   
    public function contacts()
    {
        return $this->belongsToMany(Contacts::class, 'contacts_tags', 'tag_id', 'contact_id');
    }

    public static function addTag($data){
        $existingTag = self::where('tag', $data['tag'])->first();
        if ($existingTag) {
            $tag = $existingTag;
        } else {
            $tag = self::create([
                'tag' => $data['tag'],
                'color' => $data['color'] ?? 'red', 
            ]);
        }
        return $tag;
    }

    public static function getTags(){
        return self::all();
    }

    public static function deleteTag($id)
{
    try {
        $contact = Tags::findOrFail($id);
        $contact->delete();

        return true; 
    } catch (\Exception $e) {
        return false;
    }
}

}

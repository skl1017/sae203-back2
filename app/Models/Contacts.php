<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tags;
use Illuminate\Support\Facades\Validator;


class Contacts extends Model
{

    protected $fillable = ['nom', 'prenom', 'num', 'email', 'adresse', 'note'];

    public function tags()
    {
        return $this->belongsToMany(Tags::class, 'contacts_tags', 'contact_id', 'tag_id');
    }

    /**
     * Récupère tous les contacts.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllContacts(){
        return static::all();
    }

    public static function validateData($data)
    {
        return Validator::make($data, [
            'nom' => 'required_without:prenom',
            'prenom' => 'required_without:nom',
            'num' => '',
            'email' => '',
            'adresse' => '',
            'note' => '',
            'tags' => 'nullable|array',
            'tags.*.tag' => '',
            'tags.*.color' => '',
        ])->validate();
    }
    public static function updateContactWithTags($id, $validatedData)
    {
        try {
            $contact = static::findOrFail($id);
            $contact->fill($validatedData);
            $contact->save();

            $newTags = [];
            if (isset($validatedData['tags'])) {
                $tagIds = [];
                foreach ($validatedData['tags'] as $tagName) {
                    if (!empty($tagName['tag']) && !empty($tagName['color'])) {
                        $tag = Tags::updateOrCreate(['tag' => $tagName['tag']], ['color' => $tagName['color']]);
                        $tagIds[] = $tag->id;
                        $newTags[] = $tag;
                    }
                }
                $contact->tags()->sync($tagIds);
            } else {
                $contact->tags()->sync([]); 
            }

            $contact->refresh(); 

            return ['message' => 'Contact mis à jour avec succès', 'contact' => $contact, 'tags' => $newTags];
        } catch (\Exception $e) {
            return ['error' => 'Une erreur est survenue lors de la mise à jour du contact'];
        }
    }
    public static function processContactData($data)
    {
        return self::create([
            'nom' => $data['nom'] ?? null,
            'prenom' => $data['prenom'] ?? null,
            'num' => $data['num'] ?? null,
            'email' => $data['email'] ?? null,
            'adresse' => $data['adresse'] ?? null,
            'note' => $data['note'] ?? null
        ]);
    }

    public static function deleteContact($id)
{
    try {
        $contact = Contacts::findOrFail($id);
        $contact->delete();

        return true; 
    } catch (\Exception $e) {
        return false;
    }
}

    use HasFactory;
}

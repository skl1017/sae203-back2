<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contacts;
use App\Models\Tags;
use Illuminate\Validation\ValidationException;

class ContactsController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nom' => 'required_without:prenom|string|max:255',
            'prenom' => 'required_without:nom|string|max:255',
        ]);
    }

    public function getContacts(Request $request)
{

    $nom = $request->input('nom');
    $tags = $request->input('tags');
    $num = $request->input('num');

    $query = Contacts::query();

    if ($nom) {
        $query->where(function ($query) use ($nom) {
            $query->where('nom', 'like', '%'.$nom.'%')
                  ->orWhere('prenom', 'like', '%'.$nom.'%');
        });
    }

    if ($tags) {
        
        $tagsArray = explode(',', $tags);
        $query->whereHas('tags', function ($q) use ($tagsArray) {
            $q->whereIn('tag', $tagsArray);
        });
    }

    // Exécuter la requête et récupérer les contacts correspondants
    $contacts = $query->get();

    // Construire la liste des contacts à retourner
    $contactList = [];
    foreach ($contacts as $contact) {
        $contactList[] = [
            "id" => $contact->id,
            "prenom" => $contact->prenom,
            "nom" => $contact->nom
            // Vous pouvez inclure d'autres champs de contact si nécessaire
        ];
    }

    // Retourner les contacts sous forme de réponse JSON
    return response()->json($contactList);
}




    public function getSingleContact(Request $request, $id)
    {
        $contact = Contacts::find($id);
        if (!$contact || $contact == null) {
            return response()->json(["message" => "Contact not found"], 404);
        }
        $tags = $contact->tags;

        $tagList = [];

        foreach ($tags as $tag) {
            $tagList[] = [
                "id" => $tag->id,
                "tag" => $tag->tag,
                "color" => $tag->color
            ];
        }

        $contactResult = [
            "id" => $contact->id,
            "prenom" => $contact->prenom,
            "nom" => $contact->nom,
            "num" => $contact->num,
            "email" => $contact->email,
            "adresse" => $contact->adresse,
            "note" => $contact->note
        ];

        $response = [
            "contact" => $contactResult,
            "tags" => $tagList
        ];

        return response()->json($response);
    }

    public function addContact(Request $request)
    {
        try {
            $validatedData = Contacts::validateData($request->all());

            $newContact = Contacts::processContactData($validatedData);

            if (isset($validatedData['tags'])) {
                foreach ($validatedData['tags'] as $tagName) {
                    if (!empty($tagName['tag']) && !empty($tagName['color'])) {
                        $tag = Tags::addTag(['tag' => $tagName['tag'], 'color' => $tagName['color']]);
                        $newContact->tags()->attach($tag->id);
                    }
                }
            }

            return response()->json($newContact);
        } 
        
        catch (ValidationException $e) {
            return response()->json(['error' => 'Les champs requis n\'ont pas été fournis'], 400);
        }
    }

    public function updateContactWithTags(Request $request, $id)
{
    try {
        $contact = Contacts::findOrFail($id);

        $validatedData = Contacts::validateData($request->all());

        // Mettre à jour les données du contact
        $contact->update($validatedData);
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

        return response()->json(['message' => 'Contact mis à jour avec succès', 'contact' => $contact, 'tags' => $newTags]);
    } catch (ValidationException $e) {
        return response()->json(['error' => $e->errors()], 400);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Une erreur est survenue lors de la mise à jour du contact'], 500);
    }
}

    public function deleteContact($id){
        $response = Contacts::deleteContact($id);
        return response()->json($response);
    }

}

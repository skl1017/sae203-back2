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

    $query = Contacts::query();

    if ($nom) {
        $query->where(function ($query) use ($nom) {
            $query->where('nom', 'like', '%'.$nom.'%')
                  ->orWhere('prenom', 'like', '%'.$nom.'%');
        });
    }

    if ($tags) {
        // Si les tags sont fournis sous forme de répétitions de paramètre "tags",
        // ou s'ils sont séparés par des virgules, nous les récupérons et les utilisons dans la requête.
        $tagsArray = is_array($tags) ? $tags : explode(',', $tags);
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
        // Récupérer les données de la requête
        $data = $request->all();

        // Valider les données
        $validatedData = Contacts::validateData($data);

        // Mettre à jour le contact avec ses tags associés
        $result = Contacts::updateContactWithTags($id, $validatedData);

        // Retourner une réponse JSON avec le résultat
        return response()->json($result);
    } catch (ValidationException $e) {
        // Retourner une réponse JSON avec les erreurs de validation
        return response()->json(['error' => $e->errors()], 400);
    } catch (\Exception $e) {
        // Retourner une réponse JSON en cas d'erreur interne du serveur
        return response()->json(['error' => 'Une erreur est survenue lors de la mise à jour du contact'], 500);
    }
}

    public function deleteContact($id){
        $response = Contacts::deleteContact($id);
        return response()->json($response);
    }

}

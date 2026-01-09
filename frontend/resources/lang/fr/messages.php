<?php

return [
    // Authentication
    'auth' => [
        'failed' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
        'password' => 'Le mot de passe fourni est incorrect.',
        'throttle' => 'Trop de tentatives de connexion. Veuillez réessayer dans :seconds secondes.',
    ],

    // Validation
    'validation' => [
        'required' => 'Le champ :attribute est obligatoire.',
        'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
        'min' => [
            'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
        ],
        'max' => [
            'string' => 'Le champ :attribute ne peut pas dépasser :max caractères.',
        ],
        'unique' => 'La valeur du champ :attribute est déjà utilisée.',
    ],

    // Profile
    'profile' => [
        'updated' => 'Profil mis à jour avec succès.',
        'not_found' => 'Profil non trouvé.',
    ],

    // Subscription
    'subscription' => [
        'created' => 'Abonnement créé avec succès.',
        'updated' => 'Abonnement mis à jour avec succès.',
        'deleted' => 'Abonnement supprimé avec succès.',
        'not_found' => 'Abonnement non trouvé.',
    ],

    // Media
    'media' => [
        'uploaded' => 'Média téléchargé avec succès.',
        'deleted' => 'Média supprimé avec succès.',
        'not_found' => 'Média non trouvé.',
    ],

    // Payment
    'payment' => [
        'success' => 'Paiement traité avec succès.',
        'failed' => 'Le paiement a échoué. Veuillez réessayer.',
        'insufficient_balance' => 'Solde du portefeuille insuffisant.',
    ],

    // General
    'success' => 'Opération terminée avec succès.',
    'error' => 'Une erreur est survenue. Veuillez réessayer.',
    'not_found' => 'Ressource non trouvée.',
    'unauthorized' => 'Vous n\'êtes pas autorisé à effectuer cette action.',
]; 
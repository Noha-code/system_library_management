#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Wrapper pour exécuter recomman_surprise.py avec les arguments appropriés
"""

import sys
import json
import pymysql
from recomman_surprise import LibraryRecommender

# Vérifier les arguments
if len(sys.argv) < 3:
    print("Usage: python recomman_surprise_wrapper.py user_id config_file")
    sys.exit(1)

# Récupérer les arguments
user_id = int(sys.argv[1])
config_file = sys.argv[2]

# Charger la configuration
with open(config_file, 'r') as f:
    db_config = json.load(f)

try:
    # Initialiser le recommandeur
    recommender = LibraryRecommender(db_config)
    
    # Récupérer les données
    data = recommender.fetch_data()
    
    # Prétraiter les données
    ratings_df = recommender.preprocess_data(data)
    
    # Entraîner les modèles
    recommender.train_models(ratings_df)
    
    # Générer des recommandations avancées avec tous les facteurs combinés
    recommendations = recommender.generate_final_recommendations(user_id, n=10)
    
    # Se connecter à la base de données pour stocker les recommandations
    conn = pymysql.connect(
        host=db_config['host'],
        user=db_config['user'],
        password=db_config['password'],
        db=db_config['db'],
        charset=db_config['charset']
    )
    
    try:
        with conn.cursor() as cursor:
            # Supprimer les anciennes recommandations pour cet utilisateur
            cursor.execute("DELETE FROM recommandations WHERE utilisateur_id = %s", (user_id,))
            
            # Insérer les nouvelles recommandations
            for book in recommendations:
                cursor.execute(
                    "INSERT INTO recommandations (utilisateur_id, livre_id, score, created_at) VALUES (%s, %s, %s, NOW())",
                    (user_id, book['id_livre'], book['score'])
                )
            
            # Valider les modifications
            conn.commit()
    finally:
        conn.close()
    
    print(f"Recommandations générées avec succès pour l'utilisateur {user_id}")
    sys.exit(0)

except Exception as e:
    print(f"Erreur: {e}")
    sys.exit(1)
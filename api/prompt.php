<?php
declare(strict_types=1);

/** Construit le prompt côté serveur : le client n'envoie que le CV et l'offre. */
function build_prompt(string $cv, string $offer): string
{
    $cv = mb_substr($cv, 0, 18000);
    $offer = mb_substr($offer, 0, 6000);
    $hasOffer = $offer !== '';

    $offerRule = $hasOffer
        ? "- Reprends les mots-clés de l'offre UNIQUEMENT quand ils correspondent à ce qui est déjà dans le CV. Le titre du CV doit viser ce poste.\n"
        : '';
    $offerBlock = $hasOffer ? "\nOFFRE VISÉE :\n\"\"\"\n{$offer}\n\"\"\"\n" : '';
    $against = $hasOffer ? " par rapport à l'offre visée" : '';

    return <<<PROMPT
Tu es un recruteur et coach CV expert du marché français, spécialisé dans les CV d'étudiants et de jeunes diplômés (stages, alternances, jobs étudiants, premier emploi).

Analyse le CV ci-dessous{$against}, puis réécris-le.

RÈGLES
- Note globale sur 20, exigeante mais juste : un CV étudiant moyen tourne autour de 10-12.
- Exactement 5 critères notés sur 20, dans cet ordre : "Impact et résultats", "Clarté et structure", "Mots-clés et ATS", "Orthographe et style", "Adéquation au poste". Pour chacun, un commentaire concret d'une phrase, en tutoyant.
- 3 à 4 points forts, 3 à 5 corrections prioritaires, courtes et actionnables.
- Réécriture : verbes d'action, phrases courtes, 2 à 4 puces par expérience, un profil de 2 phrases maximum en tête.
- N'INVENTE JAMAIS d'expérience, d'entreprise, de diplôme, de date, de chiffre ou de compétence absents du CV. Si un chiffre manque, formule sans chiffre. Garde les vraies coordonnées telles quelles ; laisse un champ vide ("") s'il n'existe pas.
{$offerRule}- avant_apres : 3 exemples tirés mot pour mot du CV d'origine, avec leur version réécrite.
- note_apres : la note estimée du CV réécrit.
- Tout en français.

Réponds UNIQUEMENT avec un objet JSON de cette forme :
{"note":11,"note_apres":16,
"criteres":[{"nom":"Impact et résultats","note":8,"commentaire":"..."}],
"points_forts":["..."],"a_corriger":["..."],
"avant_apres":[{"avant":"...","apres":"..."}],
"cv":{"nom":"","titre":"","contact":{"email":"","telephone":"","ville":"","lien":""},"profil":"",
"experiences":[{"poste":"","entreprise":"","lieu":"","dates":"","puces":[""]}],
"formation":[{"diplome":"","ecole":"","dates":"","details":""}],
"competences":[""],"langues":[""],"centres_interet":[""]}}
{$offerBlock}
CV D'ORIGINE :
"""
{$cv}
"""
PROMPT;
}

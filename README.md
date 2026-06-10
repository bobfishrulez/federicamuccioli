# Federica Muccioli — Sito web

## Struttura del progetto

```
/
├── index.html          → Pagina principale (one-page)
├── css/
│   └── style.css       → Tutto il CSS del sito
├── js/
│   └── main.js         → JavaScript (navbar, animazioni)
├── php/
│   └── contatto.php    → Form di contatto (da sviluppare)
└── img/
    └── (immagini webp) → Inserire qui le immagini del sito
```

## Immagini da sostituire

Nel file `index.html` cerca i commenti `<!-- Sostituire con: -->` 
per trovare i punti dove inserire le immagini reali.

Le immagini vanno salvate in formato **WebP** nella cartella `/img/`:
- `federica.webp` → foto di Federica (sezione Hero)
- `semenza.webp`  → copertine Semenza (portfolio)
- `eventi.webp`   → foto eventi (portfolio)
- `dispensa.webp` → Dispensa Magazine (portfolio)

## Dati da aggiornare

Nel file `index.html`:
- Email di contatto (cerca `federica@federicamuccioli.it`)
- Link Instagram (cerca `href="#"` nella sezione contatti)
- Dominio nel meta tag `og:url`

## Tecnologie usate

- HTML5 semantico
- CSS moderno (Grid, Flexbox, Custom Properties)
- JavaScript vanilla (no dipendenze)
- Google Fonts: Playfair Display, Lora, DM Sans

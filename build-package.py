#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Forum Numérique Congo — construction du paquet d'installation.

Génère, dans ./dist, les archives installables via l'administration WordPress :
  - forum-numerique-congo-theme.zip   (thème)
  - fnc-content-model.zip             (extension : modèle de contenu)
  - fnc-core.zip                      (extension : logique du site)
plus le guide et la note d'installation, et une archive complète du paquet.

Les archives utilisent des séparateurs « / » (norme ZIP), afin d'être
décompressées correctement par WordPress sur tout hébergement.

Usage :  python build-package.py

© 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
Développé par Vanel NGOYO ADOUMA, Lead développeur.
"""
import os
import shutil
import zipfile

ROOT = os.path.dirname(os.path.abspath(__file__))
VERSION = '1.0.36'
DIST = os.path.join(ROOT, 'dist')
STAGING = os.path.join(DIST, f'forum-numerique-congo-template-{VERSION}')

# Exclusions (cruft de développement).
EXCLUDE_DIRS = {'.git', 'node_modules', 'vendor', '.idea', '.vscode'}
EXCLUDE_FILES = {'.DS_Store', 'Thumbs.db'}


def zip_dir(src_dir, zip_path):
    """Archive un dossier, son nom de dossier servant de racine, séparateurs « / »."""
    src_dir = os.path.abspath(src_dir)
    base = os.path.basename(src_dir)
    parent = os.path.dirname(src_dir)
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zf:
        for dirpath, dirnames, filenames in os.walk(src_dir):
            dirnames[:] = [d for d in dirnames if d not in EXCLUDE_DIRS]
            for name in filenames:
                if name in EXCLUDE_FILES:
                    continue
                full = os.path.join(dirpath, name)
                rel = os.path.relpath(full, parent)
                arc = rel.replace(os.sep, '/')  # séparateurs ZIP standard
                zf.write(full, arc)


def main():
    if os.path.isdir(DIST):
        shutil.rmtree(DIST)
    os.makedirs(STAGING)

    targets = [
        ('wp-content/themes/fnc-wordpress-theme', 'forum-numerique-congo-theme.zip'),
        ('wp-content/plugins/fnc-content-model', 'fnc-content-model.zip'),
        ('wp-content/plugins/fnc-core', 'fnc-core.zip'),
    ]
    for rel_src, zip_name in targets:
        zip_dir(os.path.join(ROOT, rel_src), os.path.join(STAGING, zip_name))

    for doc in ('INSTALL.md', 'GUIDE.md'):
        shutil.copy2(os.path.join(ROOT, doc), os.path.join(STAGING, doc))

    # Archive complète du paquet (extensions + documentation).
    master = os.path.join(DIST, f'forum-numerique-congo-template-{VERSION}.zip')
    with zipfile.ZipFile(master, 'w', zipfile.ZIP_DEFLATED) as zf:
        for name in sorted(os.listdir(STAGING)):
            zf.write(os.path.join(STAGING, name), f'forum-numerique-congo-template-{VERSION}/{name}')

    print(f'Paquet construit dans : {DIST}')
    for name in sorted(os.listdir(STAGING)):
        size = os.path.getsize(os.path.join(STAGING, name)) / 1024
        print(f'  {name:<42} {size:>10,.0f} Ko')
    m = os.path.getsize(master) / 1024
    print(f'  {os.path.basename(master):<42} {m:>10,.0f} Ko  (archive complète)')


if __name__ == '__main__':
    main()

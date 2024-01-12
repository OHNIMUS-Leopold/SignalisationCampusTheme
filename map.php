<?php
/*
Template Name: Map - Léopold OHNIMUS
*/
?>




<?php get_header(); ?>

<div class="QRCode">
    <button id="btnBack">Cliquez-moi</button>
    <div id="reader"></div>
    <div id="result"></div>
</div>
<div class="mapContainer">
    <div id="container3D" style="overflow-x: hidden;"></div>
</div>

<form class="search">
    <label for="search-input" class="search-label">Recherche</label>
    <input type="text" id="search-input" class="search-bar" placeholder="Recherche">
    <button class="search-btn">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/icn/search.svg" alt="Recherche">
    </button>
</form>

<div id="draggableDiv" class="draggable">
    <span id="draggableButton" class="spanBtn"></span>
    <div class="contenu">
        <div id="rechercheDiv" class="recherche">
            <form class="search-drag">
                <label for="search-input-drag" class="search-label">Recherche</label>
                <input type="text" id="search-input-drag" class="search-bar-drag" placeholder="Recherche">
                <button class="search-btn-drag">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/icn/search.svg" alt="Recherche">
                </button>
            </form>
            <div class="search-results"></div>
        </div>
        




        <div  id="divCache"  style="display: none;">MONTRE LA DIV</div>
        <div  id="divCacheMP"  style="display: none;">MONTRE LA DIV MP</div>
    </div>  
</div>


<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script type="module">

    // Import de Three.js
    import * as THREE from 'three';

    // Import des modules complémentaires
    import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
    import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';




    // --------------------------------------------------------------------------------------------------------------------




    // Création de la scène
    const scene = new THREE.Scene();

    // Création de la caméra
    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);




    // --------------------------------------------------------------------------------------------------------------------




    // Ajout d'un gestionnaire d'événements pour le clic de la souris
    const raycaster = new THREE.Raycaster();
    const mouse = new THREE.Vector2();

    // Ajouter un seul écouteur d'événements click sur le document
    document.addEventListener('click', handleBatimentMPInteraction);

    // Fonction pour mettre à jour la position de la souris
    function onMouseMove(event) {
        // Mise à jour des coordonnées de la souris
        mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;
    }
    
    // Ajout d'un gestionnaire d'événements pour le mouvement de la souris
    window.addEventListener('mousemove', onMouseMove);




    // --------------------------------------------------------------------------------------------------------------------




    // Fonction pour changer le matériau d'un objet 3D
    function changeMaterial(object, newMaterial) {
        object.traverse((child) => {
            // Vérifie si l'enfant est un maillage (mesh)
            if (child.isMesh) {
                // Applique le nouveau matériau
                child.material = newMaterial;
            }
        });
    }




    // --------------------------------------------------------------------------------------------------------------------




    // Map 3D
    // Import de la base et des décors

    // Base

    // On garde le modèle dans une variable globale pour pouvoir l'utiliser dans la fonction animate si besoin
    let object;

    // On instancie un nouveau loader pour charger le fichier glTF/GLB
    const loader = new GLTFLoader();

    // Chargement du modèle
    loader.load(
        // Path du modèle à charger
        'http://localhost/signalisation/wp-content/uploads/2023/12/Base.glb',

        // Fonction appelée lorsque le chargement est terminé
        function ( gltf ) {

            // Sauvegarde du modèle dans une variable globale
            object = gltf.scene;

            // Reposionnement du modèle
            object.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(object, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

            // Gérer les ombres recues et projetées
            object.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true; 
                    child.receiveShadow = true;
                }
            });

            scene.add( object );
        },
        // La fonction à appeler pendant le chargement
        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },
        // La fonction à appeler en cas d'erreur
        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );

    // Création du renderer (moteur de rendu)
    const renderer = new THREE.WebGLRenderer(); // Alpha: true pour avoir un fond transparent
    renderer.setClearColor( 0xe4f1f8, 1 ); // Le fond est bleu ciel

    // Création de la map pour les ombres
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap; // default THREE.PCFShadowMap

    // Réglage de la taille du renderer
    renderer.setSize(window.innerWidth, window.innerHeight);

    // Ajout du renderer au DOM
    document.getElementById("container3D").appendChild(renderer.domElement);

    // Placement de la caméra
    camera.position.z = -10;
    camera.position.y = 10;
    camera.position.x = -20;


    // Décors
    let decors;
    const loaderDecors = new GLTFLoader();

    loaderDecors.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/Decor.glb',

        function ( gltf ) {
            decors = gltf.scene;
            decors.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(decors, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

            decors.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( decors );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );




    // Import des éléments de la map

    // Création d'un tableau pour stocker les bâtiments et leurs matériaux
    let batiments = [];


    // BU
    let bu;
    let buMaterial;
    const loaderBu = new GLTFLoader();

    loaderBu.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/BU.glb',

        function ( gltf ) {
            bu = gltf.scene;
            bu.position.set(5.5, 0, -12.5);

            // Sauvegarde du matériau de la bu dans une variable globale
            buMaterial = bu.children[0].material.clone();

            // Appel de la fonction pour changer le matériau si la bu est sélectionnée
            if (window.location.hash === '#bu-select') {
                // Changement de la couleur du batiment
                changeMaterial(bu, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                // Affichage de la div
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            // Ajout de la bu dans le tableau des bâtiments
            batiments.push({ isSelected: false, batiment: bu, material: buMaterial, numero: '10' });

            bu.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( bu );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // Centre de recherches
    let centre;
    let centreMaterial;
    const loaderCentre = new GLTFLoader();

    loaderCentre.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/Centre_Recherche.glb',

        function ( gltf ) {
            centre = gltf.scene;
            centre.position.set(5.5, 0, -12.5);

            centreMaterial = centre.children[0].material.clone();

            if (window.location.hash === '#centre-select') {
                changeMaterial(centre, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: centre, material: centreMaterial, numero: '7' });

            centre.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( centre );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // CNAM
    let cnam;
    let cnamMaterial;
    const loaderCnam = new GLTFLoader();

    loaderCnam.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/cnam.glb',

        function ( gltf ) {
            cnam = gltf.scene;
            cnam.position.set(5.5, 0, -12.5);

            cnamMaterial = cnam.children[0].material.clone();

            if (window.location.hash === '#cnam-select') {
                changeMaterial(cnam, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: cnam, material: cnamMaterial, numero: '19' });

            cnam.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( cnam );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // DEJEPS
    let dejeps;
    let dejepsMaterial;
    const loaderDejeps = new GLTFLoader();

    loaderDejeps.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/DEJEPS.glb',

        function ( gltf ) {
            dejeps = gltf.scene;
            dejeps.position.set(5.5, 0, -12.5);

            dejepsMaterial = dejeps.children[0].material.clone();

            if (window.location.hash === '#dejeps-select') {
                changeMaterial(dejeps, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: dejeps, material: dejepsMaterial, numero: '2' });

            dejeps.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( dejeps );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // GACO
    let gaco;
    let gacoMaterial;
    const loaderGaco = new GLTFLoader();

    loaderGaco.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/GACO.glb',

        function ( gltf ) {
            gaco = gltf.scene;
            gaco.position.set(5.5, 0, -12.5);

            gacoMaterial = gaco.children[0].material.clone();

            if (window.location.hash === '#gaco-select') {
                changeMaterial(gaco, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: gaco, material: gacoMaterial, numero: '9' });

            gaco.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( gaco );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // Grand Amphi
    let gamphi;
    let gamphiMaterial;
    const loaderGamphi = new GLTFLoader();

    loaderGamphi.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/Grand_Amphi.glb',

        function ( gltf ) {
            gamphi = gltf.scene;
            gamphi.position.set(5.5, 0, -12.5);

            gamphiMaterial = gamphi.children[0].material.clone();

            if (window.location.hash === '#gamphi-select') {
                changeMaterial(gamphi, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: gamphi, material: gamphiMaterial, numero: '13' });

            gamphi.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( gamphi );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // IFMS
    let ifms;
    let ifmsMaterial;
    const loaderIfms = new GLTFLoader();

    loaderIfms.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/ifms.glb',

        function ( gltf ) {
            ifms = gltf.scene;
            ifms.position.set(5.5, 0, -12.5);

            ifmsMaterial = ifms.children[0].material.clone();

            if (window.location.hash === '#ifms-select') {
                changeMaterial(ifms, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: ifms, material: ifmsMaterial, numero: '14' });

            ifms.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( ifms );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // LEA
    let lea;
    let leaMaterial;
    const loaderLea = new GLTFLoader();

    loaderLea.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/LEA.glb',

        function ( gltf ) {
            lea = gltf.scene;
            lea.position.set(5.5, 0, -12.5);

            leaMaterial = lea.children[0].material.clone();

            if (window.location.hash === '#lea-select') {
                changeMaterial(lea, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: lea, material: leaMaterial, numero: '12' });

            lea.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( lea );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // MINAMAS
    let minamas;
    let minamasMaterial;
    const loaderMinamas = new GLTFLoader();

    loaderMinamas.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/MINAMAS.glb',

        function ( gltf ) {
            minamas = gltf.scene;
            minamas.position.set(5.5, 0, -12.5);

            minamasMaterial = minamas.children[0].material.clone();

            if (window.location.hash === '#minamas-select') {
                changeMaterial(minamas, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: minamas, material: minamasMaterial, numero: '6' });

            minamas.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( minamas );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // MMI
    let mmi;
    let mmiMaterial;
    const loaderMmi = new GLTFLoader();

    loaderMmi.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/MMI.glb',

        function ( gltf ) {
            mmi = gltf.scene;
            mmi.position.set(5.5, 0, -12.5);

            mmiMaterial = mmi.children[0].material.clone();

            if (window.location.hash === '#mmi-select') {
                changeMaterial(mmi, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: mmi, material: mmiMaterial, numero: '18' });

            mmi.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( mmi );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // MP
    let mp;
    let mpMaterial1;
    let mpMaterial2;
    const loaderMp = new GLTFLoader();

    loaderMp.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/MP.glb',

        function ( gltf ) {
            mp = gltf.scene;
            mp.position.set(5.5, 0, -12.5);

            mpMaterial1 = mp.children[0].children[0].material.clone();
            mpMaterial2 = mp.children[0].children[1].material.clone();

            if (window.location.hash === '#mp-select') {
                mp.children[0].children[0].material = new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true });
                mp.children[0].children[1].material = new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true });
                const divCacheMP = document.getElementById('divCacheMP');
                divCacheMP.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            mp.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( mp );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // Numerica (entreprise)
    let numerica;
    let numericaMaterial;
    const loaderNumerica = new GLTFLoader();

    loaderNumerica.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/Numerica.glb',

        function ( gltf ) {
            numerica = gltf.scene;
            numerica.position.set(5.5, 0, -12.5);

            numericaMaterial = numerica.children[0].material.clone();

            if (window.location.hash === '#numerica-select') {
                changeMaterial(numerica, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: numerica, material: numericaMaterial, numero: '20' });

            numerica.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( numerica );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // Numerica (pouponnière)
    let numerica2;
    let numerica2Material;
    const loaderNumerica2 = new GLTFLoader();

    loaderNumerica2.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/Numerica2.glb',

        function ( gltf ) {
            numerica2 = gltf.scene;
            numerica2.position.set(5.5, 0, -12.5);

            numerica2Material = numerica2.children[0].material.clone();

            if (window.location.hash === '#numerica2-select') {
                changeMaterial(numerica2, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: numerica2, material: numerica2Material, numero: '1' });

            numerica2.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( numerica2 );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // R&T
    let rt;
    let rtMaterial;
    const loaderRt = new GLTFLoader();

    loaderRt.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/RT.glb',

        function ( gltf ) {
            rt = gltf.scene;
            rt.position.set(5.5, 0, -12.5);

            rtMaterial = rt.children[0].material.clone();

            if (window.location.hash === '#rt-select') {
                changeMaterial(rt, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: rt, material: rtMaterial, numero: '8' });

            rt.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( rt );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // RU
    let ru;
    let ruMaterial;
    const loaderRu = new GLTFLoader();

    loaderRu.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/RU.glb',

        function ( gltf ) {
            ru = gltf.scene;
            ru.position.set(5.5, 0, -12.5);

            ruMaterial = ru.children[0].material.clone();

            if (window.location.hash === '#ru-select') {
                changeMaterial(ru, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: ru, material: ruMaterial, numero: '3' });

            ru.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( ru );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // STGI
    let stgi;
    let stgiMaterial;
    const loaderStgi = new GLTFLoader();

    loaderStgi.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/STGI.glb',

        function ( gltf ) {
            stgi = gltf.scene;
            stgi.position.set(5.5, 0, -12.5);

            stgiMaterial = stgi.children[0].material.clone();

            if (window.location.hash === '#stgi-select') {
                changeMaterial(stgi, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: stgi, material: stgiMaterial, numero: '15' });

            stgi.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( stgi );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // SVE
    let sve;
    let sveMaterial;
    const loaderSve = new GLTFLoader();

    loaderSve.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/SVE.glb',

        function ( gltf ) {
            sve = gltf.scene;
            sve.position.set(5.5, 0, -12.5);

            sveMaterial = sve.children[0].material.clone();

            if (window.location.hash === '#sve-select') {
                changeMaterial(sve, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: sve, material: sveMaterial, numero: '16' });

            sve.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( sve );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // SVE Amphi
    let svea;
    let sveaMaterial;
    const loaderSvea = new GLTFLoader();

    loaderSvea.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/SVE_Amphi.glb',

        function ( gltf ) {
            svea = gltf.scene;
            svea.position.set(5.5, 0, -12.5);

            sveaMaterial = svea.children[0].material.clone();

            if (window.location.hash === '#svea-select') {
                changeMaterial(svea, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: svea, material: sveaMaterial, numero: '17' });

            svea.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( svea );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // UTBM
    let utbm;
    let utbmMaterial;
    const loaderUtbm = new GLTFLoader();

    loaderUtbm.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/UTBM.glb',

        function ( gltf ) {
            utbm = gltf.scene;
            utbm.position.set(5.5, 0, -12.5);

            utbmMaterial = utbm.children[0].material.clone();

            if (window.location.hash === '#utbm-select') {
                changeMaterial(utbm, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: utbm, material: utbmMaterial, numero: '4' });

            utbm.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( utbm );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );




    // Crous résidence
    let crous;
    let crousMaterial;
    const loaderCrous = new GLTFLoader();

    loaderCrous.load(
        'http://localhost/signalisation/wp-content/uploads/2024/01/Crous.glb',

        function ( gltf ) {
            crous = gltf.scene;
            crous.position.set(5.5, 0, -12.5);

            crousMaterial = crous.children[0].material.clone();

            if (window.location.hash === '#crous-select') {
                changeMaterial(crous, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: crous, material: crousMaterial, numero: '5' });

            crous.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( crous );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );




    // Polyclinique
    let polyclinique;
    let polycliniqueMaterial;
    const loaderPolyclinique = new GLTFLoader();

    loaderPolyclinique.load(
        'http://localhost/signalisation/wp-content/uploads/2024/01/Polyclinique.glb',

        function ( gltf ) {
            polyclinique = gltf.scene;
            polyclinique.position.set(5.5, 0, -12.5);

            polycliniqueMaterial = polyclinique.children[0].material.clone();

            if (window.location.hash === '#polyclinique-select') {
                changeMaterial(polyclinique, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: polyclinique, material: polycliniqueMaterial, numero: '0' });

            polyclinique.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( polyclinique );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );

    // --------------------------------------------------------------------------------------------------------------------




    // Affichage des panneaux de signalisation
    // Panneaux principaux

    // Panneau 1
    let panneau1;
    let panneau1Material;
    const loaderPanneau1 = new GLTFLoader();

    loaderPanneau1.load(
        'http://localhost/signalisation/wp-content/uploads/2024/01/blocpanneau.glb',

        function ( gltf ) {
            panneau1 = gltf.scene;
            panneau1.position.set(-8, 0, 2.5);
            // rotation du panneau 
            panneau1.rotation.y = -40;

            panneau1Material = panneau1.children[0].material.clone();

            if (window.location.hash === '#panneau1-select') {
                changeMaterial(panneau1, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: panneau1, material: panneau1Material, numero: '' });

            panneau1.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( panneau1 );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // Panneau 2
    let panneau2;
    let panneau2Material;
    const loaderPanneau2 = new GLTFLoader();

    loaderPanneau2.load(
        'http://localhost/signalisation/wp-content/uploads/2024/01/blocpanneau.glb',

        function ( gltf ) {
            panneau2 = gltf.scene;
            panneau2.position.set(-11.5, 0, -34);

            panneau2Material = panneau2.children[0].material.clone();

            if (window.location.hash === '#panneau2-select') {
                changeMaterial(panneau2, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: panneau2, material: panneau2Material, numero: '' });

            panneau2.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( panneau2 );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );

    
    // Panneau 3
    let panneau3;
    let panneau3Material;
    const loaderPanneau3 = new GLTFLoader();

    loaderPanneau3.load(
        'http://localhost/signalisation/wp-content/uploads/2024/01/blocpanneau.glb',

        function ( gltf ) {
            panneau3 = gltf.scene;
            panneau3.position.set(40, 0, 1.5);
            // rotation du panneau 
            panneau3.rotation.y += Math.PI / 2;

            panneau3Material = panneau3.children[0].material.clone();

            if (window.location.hash === '#panneau3-select') {
                changeMaterial(panneau3, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: panneau3, material: panneau3Material, numero: '' });

            panneau3.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( panneau3 );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );





    // Panneaux de blocs

    // Panneau 4
    let panneau4;
    let panneau4Material;
    const loaderPanneau4 = new GLTFLoader();

    loaderPanneau4.load(
        'http://localhost/signalisation/wp-content/uploads/2024/01/blocpanneau.glb',

        function ( gltf ) {
            panneau4 = gltf.scene;
            panneau4.position.set(8, 0, -2);
            // rotation du panneau 
            //panneau4.rotation.y += Math.PI / 2;

            panneau4Material = panneau4.children[0].material.clone();

            if (window.location.hash === '#panneau4-select') {
                changeMaterial(panneau4, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: panneau4, material: panneau4Material, numero: '' });

            panneau4.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( panneau4 );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // Panneau 5
    let panneau5;
    let panneau5Material;
    const loaderPanneau5 = new GLTFLoader();

    loaderPanneau5.load(
        'http://localhost/signalisation/wp-content/uploads/2024/01/blocpanneau.glb',

        function ( gltf ) {
            panneau5 = gltf.scene;
            panneau5.position.set(-20, 0, 18);
            // rotation du panneau 
            panneau5.rotation.y += Math.PI / 2;

            panneau5Material = panneau5.children[0].material.clone();

            if (window.location.hash === '#panneau5-select') {
                changeMaterial(panneau5, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: panneau5, material: panneau5Material, numero: '' });

            panneau5.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( panneau5 );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // Panneau 6
    let panneau6;
    let panneau6Material;
    const loaderPanneau6 = new GLTFLoader();

    loaderPanneau6.load(
        'http://localhost/signalisation/wp-content/uploads/2024/01/blocpanneau.glb',

        function ( gltf ) {
            panneau6 = gltf.scene;
            panneau6.position.set(1, 0, 6);
            // rotation du panneau 
            //panneau6.rotation.y += Math.PI / 2;

            panneau6Material = panneau6.children[0].material.clone();

            if (window.location.hash === '#panneau6-select') {
                changeMaterial(panneau6, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: panneau6, material: panneau6Material, numero: '' });

            panneau6.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( panneau6 );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // Panneau 7
    let panneau7;
    let panneau7Material;
    const loaderPanneau7 = new GLTFLoader();

    loaderPanneau7.load(
        'http://localhost/signalisation/wp-content/uploads/2024/01/blocpanneau.glb',

        function ( gltf ) {
            panneau7 = gltf.scene;
            panneau7.position.set(15, 0, 10);
            // rotation du panneau 
            panneau7.rotation.y += Math.PI / 2;

            panneau7Material = panneau7.children[0].material.clone();

            if (window.location.hash === '#panneau7-select') {
                changeMaterial(panneau7, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: panneau7, material: panneau7Material, numero: '' });

            panneau7.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( panneau7 );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // Panneau 8
    let panneau8;
    let panneau8Material;
    const loaderPanneau8 = new GLTFLoader();

    loaderPanneau8.load(
        'http://localhost/signalisation/wp-content/uploads/2024/01/blocpanneau.glb',

        function ( gltf ) {
            panneau8 = gltf.scene;
            panneau8.position.set(35.5, 0, 12);
            // rotation du panneau 
            // panneau8.rotation.y += Math.PI / 2;

            panneau8Material = panneau8.children[0].material.clone();

            if (window.location.hash === '#panneau8-select') {
                changeMaterial(panneau8, new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true }));
                const divCache = document.getElementById('divCache');
                divCache.style.display = 'block';
                const divRecherche = document.getElementById('rechercheDiv');
                divRecherche.style.display = 'none';
            }

            batiments.push({ isSelected: false, batiment: panneau8, material: panneau8Material, numero: '' });

            panneau8.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( panneau8 );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );




    // --------------------------------------------------------------------------------------------------------------------




    // Plan pour la géolocalisation
    // Création du plan en 2D
    const planeGeometry = new THREE.PlaneGeometry(89.5, 71.5);

    // Matériau pour la surface du plan (avec une texture si nécessaire)
    const materialSurface = new THREE.MeshBasicMaterial({ color: 0x0000ff, transparent: true, opacity: 0  }); // Bleu

    // Matériau pour les bordures du plan (rouge)
    const materialBorder = new THREE.LineBasicMaterial({ color: 0xff0000, transparent:true, opacity: 0 }); // Rouge

    // Création du mesh
    const surfacePlane = new THREE.Mesh(planeGeometry, materialSurface);
    surfacePlane.position.set(0, 0, 0);
    scene.add(surfacePlane);

    // Ajustement de la rotation du plan pour le rendre horizontal
    surfacePlane.rotation.x = -Math.PI / 2; // Rotation de 90 degrés autour de l'axe X
    scene.add(surfacePlane);

    // Création des bordures du plan en utilisant des lignes
    const edges = new THREE.EdgesGeometry(planeGeometry);
    const borderLines = new THREE.LineSegments(edges, materialBorder);
    borderLines.position.set(0, 0, 0);
    borderLines.rotation.x = -Math.PI / 2; // Rotation de 90 degrés autour de l'axe X pour les bordures
    scene.add(borderLines);


    // COORDONNEES DEP MMI

    // const topLeftLat = 47.495786;
    // const topLeftLon = 6.804308;
    // const bottomRightLat = 47.494911;
    // const bottomRightLon = 6.806146;


    // Actualisation de la position GPS de l'utilisateur dans le plan toutes les 5 secondes
    
    // Coordonnées au 4 angles du plan
    const topLeftLat = 47.496941034538736;
    const topLeftLon = 6.799992067378482;
    const bottomRightLat = 47.4946525078744;
    const bottomRightLon = 6.807431480819781;

    // Fonction de convertion des coordonnées GPS en coordonnées 3D sur le plan
    function convertGPSTo3D(lat, lon) {
        // Calcul de la position en fonction des coordonnées GPS
        const x = (lon - topLeftLon) / (bottomRightLon - topLeftLon) * planeGeometry.parameters.width - planeGeometry.parameters.width / 2;
        const z = (topLeftLat - lat) / (topLeftLat - bottomRightLat) * planeGeometry.parameters.height - planeGeometry.parameters.height / 2;
        return { x, z };
    }


    // Modèle pin
    let userCube;
    const loaderUserCube = new GLTFLoader();

    loaderUserCube.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/userpin.glb',

        function ( gltf ) {
            userCube = gltf.scene;
            userCube.position.set(0, -0.5, 0);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(userCube, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

            userCube.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add( userCube );
        },

        function xhrProgress( xhr ) {
            console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
        },

        function ( error ) {
            console.log( 'An error happened' );
            console.log( error );
        }
    );


    // Fonction pour mettre à jour la position de l'utilisateur
    function updateUserPosition(position) {
    // Appel des coordonnées GPS de l'utilisateur
    const userLat = position.coords.latitude;
    const userLon = position.coords.longitude;

    // Convertion des coordonnées GPS en coordonnées 3D
    const userCoords = convertGPSTo3D(userLat, userLon);

    // Mettez à jour la position du cube en fonction des nouvelles coordonnées
    userCube.position.set(userCoords.x, -0.5, userCoords.z);                           // (0.0,05.0)
    }

    // Fonction pour surveiller la position de l'utilisateur toutes les 5 secondes
    function watchUserPosition() {
        navigator.geolocation.getCurrentPosition(updateUserPosition);
        // console.log('Position mise à jour', userCube.position);
    }

    // Lancement de la surveillance de la position de l'utilisateur toutes les 5 secondes
    setInterval(watchUserPosition, 5000);




    // --------------------------------------------------------------------------------------------------------------------




    // Réglage des lumières sur la scène

    const light1 = new THREE.PointLight( 0xCEE6F2, 750, 200 );
    light1.position.set( 41.5, 28, 32.5 );
    light1.castShadow = false; 
    scene.add( light1 );

    const light2 = new THREE.PointLight( 0xCEE6F2, 750, 200 );
    light2.position.set( 1.5, 28, 32.5 );
    light2.castShadow = false; 
    scene.add( light2 );

    const light3 = new THREE.PointLight( 0xCEE6F2, 750, 200 );
    light3.position.set( -41.5, 28, 32.5 );
    light3.castShadow = false; 
    scene.add( light3 );

    const light4 = new THREE.PointLight( 0xCEE6F2, 750, 200 );
    light4.position.set( 41.5, 28, -0.5 );
    light4.castShadow = false; 
    scene.add( light4 );

    const light5 = new THREE.PointLight( 0xCEE6F2, 750, 200 );
    light5.position.set( 1.5, 28, -0.5 );
    light5.castShadow = false; 
    scene.add( light5 );

    const light6 = new THREE.PointLight( 0xCEE6F2, 750, 200 );
    light6.position.set( -41.5, 28, -0.5 );
    light6.castShadow = false; 
    scene.add( light6 );

    const light7 = new THREE.PointLight( 0xCEE6F2, 2000, 200 );
    light7.position.set( 41.5, 28, -32.5 );
    // Paramétrage de la map d'ombres
    light7.castShadow = true; 
    light7.shadow.mapSize.width = 1024; 
    light7.shadow.mapSize.height = 1024;  
    light7.shadow.camera.near = 0.5;  
    light7.shadow.camera.far = 500; 
    light7.shadow.bias = -0.001;
    scene.add( light7 );

    const light8 = new THREE.PointLight( 0xCEE6F2, 750, 200 );
    light8.position.set( 1.5, 28, -32.5 );
    light8.castShadow = false; 
    scene.add( light8 );

    const light9 = new THREE.PointLight( 0xCEE6F2, 750, 200 );
    light9.position.set( -41.5, 28, -32.5 );
    light9.castShadow = false; 
    scene.add( light9 );

    // Lumière ambiante
    // const ambientLight = new THREE.AmbientLight(0x000000, 0.00000000000000001);
    const ambientLight = new THREE.AmbientLight(0xdadada, 1);
    scene.add(ambientLight);




    // --------------------------------------------------------------------------------------------------------------------




    // Animation de la scène et rendu
    function animate() {
        requestAnimationFrame(animate);
        // if (object) {
        //     object.rotation.y += 0.01;
        // }

        // Mettre à jour le rayon avec la position de la souris
        raycaster.setFromCamera(mouse, camera);

        renderer.render(scene, camera);
    }

    animate();




    // --------------------------------------------------------------------------------------------------------------------




    // Ajouter des contrôles pour pouvoir bouger la caméra
    const controls = new OrbitControls(camera, renderer.domElement);
    
    // Contrôles de la caméra (optionnels)
    controls.enableDamping = true; // Ajoute un effet de smooth au mouvement
    controls.dampingFactor = 0.05; // Ajuste l'effet de smooth pour un mouvement fluide
    controls.rotateSpeed = 0.5; // Adjuste la vitesse de rotation
    controls.zoomSpeed = 0.5; // Adjuste la vitesse de zoom




// --------------------------------------------------------------------------------------------------------------------




    // Lecteur de QR CODE

    // Import de Html5QrcodeScanner
    const scanner = new Html5QrcodeScanner('reader', { 
        // Paramétrage du lecteur
        qrbox: {
            width: 300,
            height: 300,
        },  
        fps: 20, 
    });

    // Lancement du lecteur
    scanner.render(success, error);

    // Fonction pour récupérer le résultat du lecteur
    function success(result) {
        document.getElementById('result').innerHTML = `
        <h2>Résultat :</h2>
        <p><a href="${result}">${result}</a></p>
        `;

        // Suppression des données du lecteur
        scanner.clear();
        document.getElementById('reader').remove();
    }

    // En cas d'erreur
    function error(err) {
        console.error(err);
    }




// --------------------------------------------------------------------------------------------------------------------




    // Récupération de la div à afficher/cacher pour les bâtiments
    const divRecherche = document.getElementById('rechercheDiv');

    var searchForm = document.querySelector('.search');

    // Gestion du glisser/déposer de la div

    // Définition des variables
    var draggableDiv = document.getElementById('draggableDiv');
    var draggableButton = document.getElementById('draggableButton');
    var offsetY, isDragging = false;

    //var inputBtn = document.getElementsByClassName('search');

    // Événements souris
    draggableDiv.addEventListener('mousedown', startDragging);
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', stopDragging);

    // Événements tactiles
    draggableDiv.addEventListener('touchstart', function(e) {
        // Utiliser le premier doigt
        startDragging(e.touches[0]);
    });

    document.addEventListener('touchmove', function(e) {
        // Utiliser le premier doigt
        drag(e.touches[0]);
    });

    document.addEventListener('touchend', stopDragging);

    // Fonctions pour gérer le glisser/déposer
    function startDragging(e) {
        isDragging = true;
        offsetY = e.clientY - draggableDiv.getBoundingClientRect().top;
        draggableDiv.style.cursor = 'grabbing';

        // Empêcher le comportement par défaut pour éviter le défilement indésirable sur les appareils tactiles
        // e.preventDefault();
    }

    function drag(e) {
        if (!isDragging) return;
        var y = e.clientY - offsetY;
        
        // Limiter la position maximale vers le haut à 200 pixels du haut
        y = Math.max(y, 300); //200

        // Limiter la position maximale vers le bas à 50 pixels du bas
        var maxHeight = window.innerHeight - 21; //50
        y = Math.min(y, maxHeight);

        draggableDiv.style.top = y + 'px';

        // Afficher le searchForm si draggableDiv est à 100px du bas
        var distanceFromBottom = maxHeight - y;
        if (distanceFromBottom <= 100) {
            searchForm.style.display = 'flex'; 
            divRecherche.style.display = 'none';
        } else {
            searchForm.style.display = 'none';
            divRecherche.style.display = 'block';
            // const divCache = document.getElementById('divCache');
            // divCache.style.display = 'none';
            // const divCacheMP = document.getElementById('divCacheMP');
            // divCache.style.display = 'none';
        }

        const divCache = document.getElementById('divCache');
        
        const divCacheMP = document.getElementById('divCacheMP');
        
        if(divCache.style.display == 'block' || divCacheMP.style.display == 'block'){
            searchForm.style.display = 'flex'; 
            divRecherche.style.display = 'none';
        }

        // Empêcher le comportement par défaut pour éviter le défilement indésirable sur les appareils tactiles
        // e.preventDefault();
    }

    function stopDragging() {
        isDragging = false;
        draggableDiv.style.cursor = 'grab';
    }

    // Bouton pour déplacer la div vers le haut
    draggableButton.addEventListener('click', function() {
        draggableDiv.style.top = '300px'; //200
        searchForm.style.display = 'none';
        divRecherche.style.display = 'block';
    });

    // Bouton pour déplacer la div vers le haut
    searchForm.addEventListener('click', function() {
        draggableDiv.style.top = '300px'; //200
        searchForm.style.display = 'none';
        document.getElementById('search-input-drag').focus();
        divRecherche.style.display = 'block';
    });


    // --------------------------------------------------------------------------------------------------------------------




    // Gestion de l'interaction avec les bâtiments


    // Récupération de la div à afficher/cacher pour les bâtiments
    const divCache = document.getElementById('divCache');

    // Fonction pour gérer l'interaction avec les bâtiments
    function handleBatimentInteraction() {

        // Prévenir le comportement par défaut
        event.preventDefault();

        // Récupération de l'ID de l'élément cliqué et de ses ancêtres
        const clickedElementId = event.target.id;
        const draggableDivAncestorId = event.target.closest('#draggableDiv')?.id;

        // Si l'élément cliqué ou l'un de ses ancêtres est dans la div draggableDiv, ne rien faire
        if (clickedElementId === 'draggableDiv' || draggableDivAncestorId === 'draggableDiv') {
            return;
        
        // Sinon, gérer l'interaction avec les bâtiments
        } else {
            // Calcul de la position de la souris dans le viewport
            mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
            mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;

            // Mise à jour du rayon avec la position de la souris
            raycaster.setFromCamera(mouse, camera);

            // Parcourt de tous les bâtiments
            const intersects = batiments
                .map(batiment => ({ batiment, intersect: raycaster.intersectObject(batiment.batiment, true) }))
                .filter(({ intersect }) => intersect.length > 0)
                .sort((a, b) => a.intersect[0].distance - b.intersect[0].distance);

            // Désélectionner tous les bâtiments
            batiments.forEach(batiment => {
                batiment.batiment.children[0].material = batiment.material;
                batiment.isSelected = false;
            });

            // Si il y a des intersections, sélectionner le bâtiment le plus proche
            if (intersects.length > 0) {
                const { batiment } = intersects[0];
                batiment.batiment.children[0].material = new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true });
                batiment.isSelected = true;
                filtrerContenuParNumero(batiment.numero);
                // Afficher la div
                divCache.style.display = 'block';
                draggableDiv.style.top = '600px'; //200
                divRecherche.style.display = 'none';
            } else {
                // Si aucun bâtiment n'a été cliqué, cacher la div
                divCache.style.display = 'none';
                divRecherche.style.display = 'block';
            }
        }
    }




    // Fonction pour gérer l'interaction avec le bâtiment MP
    function handleBatimentMPInteraction(event) {

        // Prévenir le comportement par défaut
        event.preventDefault();

        // Récupération de l'ID de l'élément cliqué et de ses ancêtres
        const clickedElementId = event.target.id;
        const draggableDivAncestorId = event.target.closest('#draggableDiv')?.id;

        // Si l'élément cliqué ou l'un de ses ancêtres est dans la div draggableDiv, ne rien faire
        if (clickedElementId === 'draggableDiv' || draggableDivAncestorId === 'draggableDiv') {
            return;

        // Sinon, gérer l'interaction avec le bâtiment MP
        } else {

            // Calcul de la position de la souris dans le viewport
            mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
            mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;

            // Mise à jour du rayon avec la position de la souris
            raycaster.setFromCamera(mouse, camera);

            // Récupération de la div à afficher/cacher
            const divCacheMP = document.getElementById('divCacheMP');

            // Variable pour savoir si le bâtiment mp a été cliqué
            let selectedMP = false;
            
            // Vérification si le rayon intersecte le bâtiment
            let intersects = raycaster.intersectObject(mp, true);

            // Si le rayon intersecte le bâtiment, le sélectionner
            if (intersects.length > 0) {
                selectedMP = true;

            // Sinon, désélectionner le bâtiment
            } else {
                // Désélectionner le bâtiment si ce n'est pas celui qui a été cliqué
                mp.children[0].children[0].material = mpMaterial1;
                mp.children[0].children[1].material = mpMaterial1;
                selectedMP = false;
                divCacheMP.style.display = 'none';
                divRecherche.style.display = 'block';

                // Réitérer sur les autres bâtiments
                handleBatimentInteraction();
            }

            // Si un bâtiment a été cliqué, le sélectionner
            if (selectedMP) {

                // Désélectionner tous les bâtiments
                batiments.forEach(batiment => {
                    batiment.batiment.children[0].material = batiment.material;
                    batiment.isSelected = false;
                });

                // Cacher la div
                divCache.style.display = 'none';

                filtrerContenuParNumeroMP();

                // Sélectionner le bâtiment mp
                mp.children[0].children[0].material = new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true });
                mp.children[0].children[1].material = new THREE.MeshPhongMaterial({ color: 0xff6161, side: THREE.DoubleSide, depthTest: true, depthWrite: true });
                selectedMP = true;

                // Afficher la div mp
                divCacheMP.style.display = 'block';
                draggableDiv.style.top = '600px'; //200
                divRecherche.style.display = 'none';
            }
        }   
    }




    // --------------------------------------------------------------------------------------------------------------------




    // Recherche

    // Utilisation de délégués d'événements pour les éléments dynamiques
    $(document).on('input', '#search-input-drag', function () {
        // Attente de la fin de la saisie dans la barre de recherche
        $('#search-input-drag').on('input', function () {
            // Récupération de la valeur de la barre de recherche
            var searchTerm = $(this).val();

            // Requête AJAX vers le serveur WordPress
            $.ajax({
                type: 'GET',
                url: '<?php echo admin_url('admin-ajax.php'); ?>', // Utilisez la fonction admin_url pour obtenir le chemin correct
                data: {
                    action: 'search_departements', // Nom de l'action côté serveur
                    search_term: searchTerm
                },
                success: function (response) {
                    // Affichage des résultats dans la div appropriée
                    $('.search-results').html(response);
                }
            });
        });
    });

    // Utilisation de délégués d'événements pour les liens générés dynamiquement
    $(document).on('click', '.search-results a', function (e) {
        e.preventDefault(); // Empêche le comportement par défaut du lien
        window.location.href = $(this).attr('href'); // Redirige vers l'URL du lien
    });




    // Afficher les données des bâtimens dans la div
    
    // Utilisation de délégués d'événements pour les liens générés dynamiquement dans divCache
    $(document).on('click', '#divCache a', function (e) {
        e.preventDefault(); // Empêche le comportement par défaut du lien
        window.location.href = $(this).attr('href'); // Redirige vers l'URL du lien
    });

    // Fonction pour filtrer le contenu côté serveur
    function filtrerContenuParNumero(numero) {
        // Requête AJAX vers le serveur WordPress
        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>', // Assurez-vous que 'ajaxurl' est défini dans votre script WordPress
            data: {
                action: 'filtrer_contenu_par_numero',
                numero: numero
            },
            success: function (response) {
                // 'response' contient le résultat de la requête AJAX côté serveur
                // console.log(response);
                $('#divCache').html(response);

                // Utilisez la réponse pour afficher le contenu dans votre application JavaScript
            }
        });
    }




    // Cas de MP

    // Utilisation de délégués d'événements pour les liens générés dynamiquement dans divCacheMP
    $(document).on('click', '#divCacheMP a', function (e) {
        e.preventDefault(); // Empêche le comportement par défaut du lien
        window.location.href = $(this).attr('href'); // Redirige vers l'URL du lien
    });

    // Fonction pour filtrer le contenu côté serveur
    function filtrerContenuParNumeroMP() {
        // Requête AJAX vers le serveur WordPress
        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>', // Assurez-vous que 'ajaxurl' est défini dans votre script WordPress
            data: {
                action: 'filtrer_contenu_par_numeroMP',
                numero: '11'
            },
            success: function (response) {
                // 'response' contient le résultat de la requête AJAX côté serveur
                // console.log(response);
                $('#divCacheMP').html(response);

                // Utilisez la réponse pour afficher le contenu dans votre application JavaScript

                // Si vous avez des liens dans le contenu ajouté dynamiquement,
                // assurez-vous qu'ils soient également pris en charge par le délégué d'événements
                $('#divCacheMP a').on('click', function (e) {
                    e.preventDefault();
                    window.location.href = $(this).attr('href');
                });
            }
        });
    }




</script>
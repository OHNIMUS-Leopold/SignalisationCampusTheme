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
        <div  id="divCache"  style="display: none;">MONTRE LA DIV</div>
    </div>  
</div>




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




    // Images mise en volume dans un cube

    // Création de la géométrie du cube
    const cubeGeometry = new THREE.BoxGeometry(1, 1, 1);
    //const cubeGeometry = new THREE.BoxGeometry(2, 2, 0.1);
    // Création des textures pour chaque face du cube
    const materialImg = [
      new THREE.MeshPhongMaterial({ color: 0x00ff00 }),
      new THREE.MeshPhongMaterial({ color: 0x00ff00 }),
      new THREE.MeshPhongMaterial({ color: 0x00ff00 }),
      new THREE.MeshPhongMaterial({ color: 0x00ff00 }),
      new THREE.MeshPhongMaterial({ color: 0x00ff00 }),
      new THREE.MeshPhongMaterial({ color: 0x00ff00 }),
      //new THREE.MeshPhongMaterial({ map: new THREE.TextureLoader().load('http://localhost/signalisation/wp-content/uploads/2023/10/bsness.jpg') }),
      //new THREE.MeshPhongMaterial({ map: new THREE.TextureLoader().load('http://localhost/signalisation/wp-content/uploads/2023/10/eh.jpg') }),
    ];

    // Vérification de l'ancre dans l'URL et changement de la couleur du cube en fonction
    if (window.location.hash === '#yellow') {
        // Changement de la couleur du cube
        materialImg[0] = new THREE.MeshPhongMaterial({ color: 0xffff00 }); // Yellow
        materialImg[1] = new THREE.MeshPhongMaterial({ color: 0xffff00 }); // Yellow
        materialImg[2] = new THREE.MeshPhongMaterial({ color: 0xffff00 }); // Yellow
        materialImg[3] = new THREE.MeshPhongMaterial({ color: 0xffff00 }); // Yellow
        materialImg[4] = new THREE.MeshPhongMaterial({ color: 0xffff00 }); // Yellow
        materialImg[5] = new THREE.MeshPhongMaterial({ color: 0xffff00 }); // Yellow
    }

    // Création du cube avec la géométrie et les textures
    const imageCube = new THREE.Mesh(cubeGeometry, materialImg);
    // Positionnage du cube dans la scène
    imageCube.position.set(1, 1, 1);
    // Ajout du cube à la scène
    scene.add(imageCube);

    // Ombres du cube
    imageCube.castShadow = true; 
    imageCube.receiveShadow = true; 




    // --------------------------------------------------------------------------------------------------------------------




    // Ajout d'un gestionnaire d'événements pour le clic de la souris
    const raycaster = new THREE.Raycaster();
    const mouse = new THREE.Vector2();
    window.addEventListener('click', onClick);
    
    // Fonction pour mettre à jour la position de la souris
    function onMouseMove(event) {
        // Mise à jour des coordonnées de la souris
        mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;
    }
    
    // Ajout d'un gestionnaire d'événements pour le mouvement de la souris
    window.addEventListener('mousemove', onMouseMove);




    // --------------------------------------------------------------------------------------------------------------------



/*
    // Fonction pour gérer le clic de la souris et afficher/cacher les éléments
    function onClick(event) {
        // Calcul de la position de la souris dans le viewport
        const rect = renderer.domElement.getBoundingClientRect();
        const x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        const y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

        // Mise à jour des coordonnées de la souris
        mouse.x = x;
        mouse.y = y;

        // Mise à jour du rayon avec la position de la souris
        raycaster.setFromCamera(mouse, camera);

        // On vérifie si le rayon intersecte le cube
        const intersects = raycaster.intersectObject(imageCube);

        // Si le rayon intersecte le cube, on affiche/cache le div
        if (intersects.length > 0) {
            // Récupération de la div à afficher/cacher
            const divCache = document.getElementById('divCache');
            // Si le div est caché, on l'affiche, et vice-versa
            if (divCache.style.display === 'none') {
                divCache.style.display = 'block';
            } else {
                divCache.style.display = 'none';
            }
        }
    }

*/


    // Variable pour suivre l'état du cube
    let cubeIsYellow = false;

    // Fonction pour gérer le clic de la souris et afficher/cacher les éléments
    function onClick(event) {
        // Récupération de l'ID de l'élément cliqué et de ses ancêtres
        const clickedElementId = event.target.id;
        const draggableDivAncestorId = event.target.closest('#draggableDiv')?.id;

        // Si l'élément cliqué ou l'un de ses ancêtres est dans la div draggableDiv, ne rien faire
        if (clickedElementId === 'draggableDiv' || draggableDivAncestorId === 'draggableDiv') {
            return;

        // Sinon, on applique le comportement normal
        } else {
            // Calcul de la position de la souris dans le viewport
            const rect = renderer.domElement.getBoundingClientRect();
            const x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
            const y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

            // Mise à jour des coordonnées de la souris
            mouse.x = x;
            mouse.y = y;

            // Mise à jour du rayon avec la position de la souris
            raycaster.setFromCamera(mouse, camera);

            // On vérifie si le rayon intersecte le cube
            const intersects = raycaster.intersectObject(imageCube);

            // Récupération de la div à afficher/cacher
            const divCache = document.getElementById('divCache');

            // Si le rayon intersecte le cube, on change la couleur du cube et on affiche la div
            if (intersects.length > 0) {
                const cube = intersects[0].object;

                // Toggle entre vert et jaune
                if (!cubeIsYellow) {
                    // Changement de la couleur du cube en jaune
                    cube.material.forEach(material => (material.color.setHex(0xffff00)));
                    // Affichage de la div
                    divCache.style.display = 'block';
                    // Mise à jour de l'état du cube
                    cubeIsYellow = true;
                }
            } else {
                // Si le clic est en dehors du cube, on remet le cube en vert et on cache la div
                imageCube.material.forEach(material => (material.color.setHex(0x00ff00)));
                divCache.style.display = 'none';
                // Mise à jour de l'état du cube
                cubeIsYellow = false;
            }
        }
    }




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


    // On garde le modèle dans une variable globale pour pouvoir l'utiliser dans la fonction animate si besoin
    let object;

    // On instancie un nouveau loader pour charger le fichier glTF/GLB
    const loader = new GLTFLoader();

    // Chargement du modèle
    loader.load(
        //'http://localhost/signalisation/wp-content/uploads/2023/10/scene.gltf',
        //'http://localhost/signalisation/wp-content/uploads/2023/11/map_test1.glb',
        'http://localhost/signalisation/wp-content/uploads/2023/12/Map_sombre.glb',
        // Path du modèle à charger
        //'/shiba/scene.gltf',
        //'/mapcampus/map_test1.glb',
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
                    object.castShadow = true; 
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
    renderer.setClearColor( 0xCEE6F2, 1 ); // Le fond est bleu ciel

    // Création de la map pour les ombres
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap; // default THREE.PCFShadowMap

    // Réglage de la taille du renderer
    renderer.setSize(window.innerWidth, window.innerHeight);

    // Ajout du renderer au DOM
    document.getElementById("container3D").appendChild(renderer.domElement);

    // Placement de la caméra
    //camera.position.z = 5;
    camera.position.z = 60;
    //camera.position.x = 200;
    // camera.position.y = 200;




    // --------------------------------------------------------------------------------------------------------------------



    // Map 3D
    // Import des éléments de la map

    // BU
    let bu;
    const loaderBu = new GLTFLoader();

    loaderBu.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/BU.glb',

        function ( gltf ) {
            bu = gltf.scene;
            bu.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(bu, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderCentre = new GLTFLoader();

    loaderCentre.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/Centre_Recherche.glb',

        function ( gltf ) {
            centre = gltf.scene;
            centre.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(centre, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderCnam = new GLTFLoader();

    loaderCnam.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/cnam.glb',

        function ( gltf ) {
            cnam = gltf.scene;
            cnam.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(cnam, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderDejeps = new GLTFLoader();

    loaderDejeps.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/DEJEPS.glb',

        function ( gltf ) {
            dejeps = gltf.scene;
            dejeps.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(dejeps, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderGaco = new GLTFLoader();

    loaderGaco.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/GACO.glb',

        function ( gltf ) {
            gaco = gltf.scene;
            gaco.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(gaco, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderGamphi = new GLTFLoader();

    loaderGamphi.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/Grand_Amphi.glb',

        function ( gltf ) {
            gamphi = gltf.scene;
            gamphi.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(gamphi, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderIfms = new GLTFLoader();

    loaderIfms.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/ifms.glb',

        function ( gltf ) {
            ifms = gltf.scene;
            ifms.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(ifms, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderLea = new GLTFLoader();

    loaderLea.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/LEA.glb',

        function ( gltf ) {
            lea = gltf.scene;
            lea.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(lea, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderMinamas = new GLTFLoader();

    loaderMinamas.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/MINAMAS.glb',

        function ( gltf ) {
            minamas = gltf.scene;
            minamas.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(minamas, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderMmi = new GLTFLoader();

    loaderMmi.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/MMI.glb',

        function ( gltf ) {
            mmi = gltf.scene;
            mmi.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(mmi, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderMp = new GLTFLoader();

    loaderMp.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/MP.glb',

        function ( gltf ) {
            mp = gltf.scene;
            mp.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(mp, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderNumerica = new GLTFLoader();

    loaderNumerica.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/Numerica.glb',

        function ( gltf ) {
            numerica = gltf.scene;
            numerica.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(numerica, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderNumerica2 = new GLTFLoader();

    loaderNumerica2.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/Numerica2.glb',

        function ( gltf ) {
            numerica2 = gltf.scene;
            numerica2.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(numerica2, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderRt = new GLTFLoader();

    loaderRt.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/RT.glb',

        function ( gltf ) {
            rt = gltf.scene;
            rt.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(rt, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderRu = new GLTFLoader();

    loaderRu.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/RU.glb',

        function ( gltf ) {
            ru = gltf.scene;
            ru.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(ru, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderStgi = new GLTFLoader();

    loaderStgi.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/STGI.glb',

        function ( gltf ) {
            stgi = gltf.scene;
            stgi.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(stgi, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderSve = new GLTFLoader();

    loaderSve.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/SVE.glb',

        function ( gltf ) {
            sve = gltf.scene;
            sve.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(sve, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderSvea = new GLTFLoader();

    loaderSvea.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/SVE_Amphi.glb',

        function ( gltf ) {
            svea = gltf.scene;
            svea.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(svea, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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
    const loaderUtbm = new GLTFLoader();

    loaderUtbm.load(
        'http://localhost/signalisation/wp-content/uploads/2023/12/UTBM.glb',

        function ( gltf ) {
            utbm = gltf.scene;
            utbm.position.set(5.5, 0, -12.5);

            // Appel de la fonction pour changer le matériau
            // changeMaterial(utbm, new THREE.MeshPhongMaterial({ color: 0xff0000 }));

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




    // --------------------------------------------------------------------------------------------------------------------




    // Plan pour la géolocalisation
    // Création du plan en 2D
    const planeGeometry = new THREE.PlaneGeometry(5, 5);

    // Matériau pour la surface du plan (avec une texture si nécessaire)
    const materialSurface = new THREE.MeshBasicMaterial({ color: 0x0000ff, transparent: true, opacity: 0.1  }); // Bleu

    // Matériau pour les bordures du plan (rouge)
    const materialBorder = new THREE.LineBasicMaterial({ color: 0xff0000 }); // Rouge

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
    const topLeftLat = 47.49535333285176;
    const topLeftLon = 6.8052503840158;
    const bottomRightLat = 47.49523870889571;
    const bottomRightLon = 6.805133037367076;

    // Fonction de convertion des coordonnées GPS en coordonnées 3D sur le plan
    function convertGPSTo3D(lat, lon) {
        // Calcul de la position en fonction des coordonnées GPS
        const x = (lon - topLeftLon) / (bottomRightLon - topLeftLon) * planeGeometry.parameters.width - planeGeometry.parameters.width / 2;
        const z = (topLeftLat - lat) / (topLeftLat - bottomRightLat) * planeGeometry.parameters.height - planeGeometry.parameters.height / 2;
        return { x, z };
    }

    // Création d'un cube représentant l'utilisateur
    const userGeometry = new THREE.BoxGeometry(0.1, 0.1, 0.1);
    const userMaterial = new THREE.MeshBasicMaterial({ color: 0xff0000 }); // Rouge
    const userCube = new THREE.Mesh(userGeometry, userMaterial);
    userCube.position.set(0, 0, 0); // Initialise la position du cube à l'origine 0 0 0        // (0.0,05.0)

    // Ajout du cube à la scène
    scene.add(userCube);

    // Fonction pour mettre à jour la position de l'utilisateur
    function updateUserPosition(position) {
    // Appel des coordonnées GPS de l'utilisateur
    const userLat = position.coords.latitude;
    const userLon = position.coords.longitude;

    // Convertion des coordonnées GPS en coordonnées 3D
    const userCoords = convertGPSTo3D(userLat, userLon);

    // Mettez à jour la position du cube en fonction des nouvelles coordonnées
    userCube.position.set(userCoords.x, 0, userCoords.z);                           // (0.0,05.0)
    }

    // Fonction pour surveiller la position de l'utilisateur toutes les 5 secondes
    function watchUserPosition() {
    navigator.geolocation.getCurrentPosition(updateUserPosition);
    console.log('Position mise à jour');
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

    const light7 = new THREE.PointLight( 0xCEE6F2, 1500, 200 );
    light7.position.set( 41.5, 28, -32.5 );
    // Paramétrage de la map d'ombres
    light7.castShadow = true; 
    light7.shadow.mapSize.width = 1024; 
    light7.shadow.mapSize.height = 1024;  
    light7.shadow.camera.near = 0.5;  
    light7.shadow.camera.far = 500; 
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
    const ambientLight = new THREE.AmbientLight(0xffffff, 1);
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




    // Vérification de la couleur du cube au chargement de la page
    const initialCubeColor = imageCube.material[0].color.getHex();

    // Si le cube est jaune, on affiche la div
    if (initialCubeColor === 0xffff00) {
        const divCache = document.getElementById('divCache');
        divCache.style.display = 'block';
    }



    
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




    // Gestion du glisser/déposer de la div

    // Définition des variables
    var draggableDiv = document.getElementById('draggableDiv');
    var draggableButton = document.getElementById('draggableButton');
    var offsetY, isDragging = false;

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
        y = Math.max(y, 200);

        // Limiter la position maximale vers le bas à 50 pixels du bas
        var maxHeight = window.innerHeight - 50;
        y = Math.min(y, maxHeight);

        draggableDiv.style.top = y + 'px';

        // Empêcher le comportement par défaut pour éviter le défilement indésirable sur les appareils tactiles
        // e.preventDefault();
    }

    function stopDragging() {
        isDragging = false;
        draggableDiv.style.cursor = 'grab';
    }

    // Bouton pour déplacer la div vers le haut
    draggableButton.addEventListener('click', function() {
        draggableDiv.style.top = '200px';
    });




</script>
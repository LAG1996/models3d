<!DOCTYPE html>
<html lang="en">
    <head>
        <title>three.js webgl - materials - lightmap</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
        <style>
            body {
                background:#fff;
                padding:0;
                margin:0;
                overflow:hidden;
                font-family:georgia;
                text-align:center;
            }
            h1 { }
            a { color:skyblue }
        </style>
    </head>

    <body>
        <script src="js/three.min.js"></script>

        <script src="js/TrackballControls.js"></script>

        <script src="js/Detector.js"></script>
        <script src="js/stats.min.js"></script>

        <script type="x-shader/x-vertex" id="vertexShader">
            varying vec3 worldPosition;
            void main() {
                vec4 mPosition = modelMatrix * vec4( position, 1.0 );
                gl_Position = projectionMatrix * modelViewMatrix * vec4( position, 1.0 );
                worldPosition = mPosition.xyz;
            }
        </script>

        <script type="x-shader/x-fragment" id="fragmentShader">
            uniform vec3 topColor;
            uniform vec3 bottomColor;
            uniform float offset;
            uniform float exponent;
            varying vec3 worldPosition;
            void main() {
                float h = normalize( worldPosition + offset ).y;
                gl_FragColor = vec4( mix( bottomColor, topColor, max( pow( h, exponent ), 0.0 ) ), 1.0 );
            }
        </script>

        <script>
            var SCREEN_WIDTH = window.innerWidth;
            var SCREEN_HEIGHT = window.innerHeight;
            var container,stats;
            var camera, scene, renderer;
            var clock = new THREE.Clock();
            init();
            animate();
            function init() {
                container = document.createElement( 'div' );
                document.body.appendChild( container );
                // CAMERA
                camera = new THREE.PerspectiveCamera( 40, SCREEN_WIDTH / SCREEN_HEIGHT, 1, 10000 );
                camera.position.x = 700;
                camera.position.z = -500;
                camera.position.y = 180;
                // SCENE
                scene = new THREE.Scene();
                scene.fog = new THREE.Fog( 0xfafafa, 1000, 10000 );
                scene.fog.color.setRGB( 0.6, 0.125, 1 );
                // CONTROLS
                controls = new THREE.TrackballControls( camera );
                controls.target.z = 150;
                // LIGHTS
                var directionalLight = new THREE.DirectionalLight( 0xffffff, 1.475 );
                directionalLight.position.set( 100, 100, -100 );
                scene.add( directionalLight );
                var hemiLight = new THREE.HemisphereLight( 0xffffff, 0xffffff, 1.25 );
                hemiLight.color.setRGB( 0.6, 0.45, 1 );
                hemiLight.groundColor.setRGB( 0.1, 0.45, 0.95 );
                hemiLight.position.y = 500;
                scene.add( hemiLight );
                // SKYDOME
                var vertexShader = document.getElementById( 'vertexShader' ).textContent;
                var fragmentShader = document.getElementById( 'fragmentShader' ).textContent;
                var uniforms = {
                    topColor:      { type: "c", value: new THREE.Color( 0x0077ff ) },
                    bottomColor: { type: "c", value: new THREE.Color( 0xffffff ) },
                    offset:         { type: "f", value: 400 },
                    exponent:     { type: "f", value: 0.6 }
                }
                uniforms.topColor.value.copy( hemiLight.color );
                scene.fog.color.copy( uniforms.bottomColor.value );
                var skyGeo = new THREE.SphereGeometry( 4000, 32, 15 );
                var skyMat = new THREE.ShaderMaterial( { vertexShader: vertexShader, fragmentShader: fragmentShader, uniforms: uniforms, side: THREE.BackSide } );
                var sky = new THREE.Mesh( skyGeo, skyMat );
                scene.add( sky );
                // RENDERER
                renderer = new THREE.WebGLRenderer( { antialias: true, alpha: false, clearColor: 0xfafafa, clearAlpha: 1 } );
                renderer.setSize( SCREEN_WIDTH, SCREEN_HEIGHT );
                renderer.domElement.style.position = "relative";
                container.appendChild( renderer.domElement );
                renderer.setClearColor( scene.fog.color, 1 );
                renderer.gammaInput = true;
                renderer.gammaOutput = true;
                renderer.physicallyBasedShading = true;
                // STATS
                stats = new Stats();
                stats.domElement.style.position = 'absolute';
                stats.domElement.style.top = '0px';
                stats.domElement.style.zIndex = 100;
                container.appendChild( stats.domElement );
                stats.domElement.children[ 0 ].children[ 0 ].style.color = "#abc";
                stats.domElement.children[ 0 ].style.background = "transparent";
                stats.domElement.children[ 0 ].children[ 1 ].style.display = "none";
                // MODEL
                var loader = new THREE.JSONLoader();
                var callback = function( geometry ) { createScene( geometry,  0, 0, 0, 0, 100 ) };
                loader.load( "js/lightmap/lightmap.json", callback );
                //
                window.addEventListener( 'resize', onWindowResize, false );
            }
            function onWindowResize() {
                camera.aspect = window.innerWidth / window.innerHeight;
                camera.updateProjectionMatrix();
                renderer.setSize( window.innerWidth, window.innerHeight );
            }
            function createScene( geometry, x, y, z, b, s ) {
                var mesh = new THREE.Mesh( geometry, new THREE.MeshFaceMaterial() );
                mesh.position.set( x, y, z );
                mesh.scale.set( s, s, s );
                scene.add( mesh );
            }
            //
            function animate() {
                requestAnimationFrame( animate );
                render();
                stats.update();
            }
            function render() {
                var delta = clock.getDelta();
                controls.update( delta );
                renderer.render( scene, camera );
            }
        </script>

    </body>
</html>

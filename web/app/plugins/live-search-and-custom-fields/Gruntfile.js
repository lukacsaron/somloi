module.exports = function( grunt ) {

	var angFiles = [
		'app/app.js',
		'app/controllers/*.js',
		'app/services/*.js',
		'app/directives/*.js'
		
];

	var backendJS = [
		'assets/js/filter-style.js',
		'assets/js/px_source.js',
		'assets/js/lscf_backend_functions.js',
		'assets/js/import-export.js',
		'assets/js/px_custom-fields.js'
		
	];

	var frontendJS = [
		'assets/js/lib/customRange.js',
		'assets/js/main.js',
		'assets/js/frontend-fields.js'
	];

	var vendorJS = [
		'assets/vendor/mustache/mustache.js',
		'assets/vendor/slick/slick.min.js',
		'assets/vendor/colorpicker/colors.js',
		'assets/vendor/colorpicker/jqColorPicker.js',
		'assets/vendor/jquery-custom-scrollbar-master/jquery.custom-scrollbar.js'


	]

	var jsFiles = [].concat(backendJS, frontendJS);

	// Project configuration.
	grunt.initConfig({
	
        //retrieve the files using writing rule (**/*)
        jshint:{
          all:angFiles  
        },
        ngAnnotate: {
            options: {
                singleQuotes: true
            },
            app: {
                files: {
                    'assets/grunt/appFactory.js': angFiles
                }
            }
        },
        // concatenate all JS files into 1 single file 
        concat: {
            
            dist1:{
                options: { "separator": ";" },
                src: backendJS,
                dest: "assets/grunt/sourceConcat.js"
            },    
        
            dist2:{
                options: { "separator": ";" },
                src: ["assets/grunt/appFactory.js"],
                dest: "assets/grunt/appConcat.js"
            },
            
            dist3:{
                options: { "separator": ";" },
                src: frontendJS,
                dest: "assets/grunt/mainConcat.js"
            },
            dist4:{
                options: { "separator": ";" },
                src: vendorJS,
                dest: "assets/grunt/vendorConcat.js"
            }
            
        },
        
        
        
        // minify the js scripts
        uglify: {
            js:{
                files:{
                    'assets/source.js':'assets/grunt/sourceConcat.js',
                    'assets/main.js':'assets/grunt/mainConcat.js',
                    'assets/app.js':'assets/grunt/appConcat.js',
                    'assets/vendor.js':'assets/grunt/vendorConcat.js'
                }
            }        
        },
        watch: {
            
            scripts: {
                files: [].concat( vendorJS, [].concat(jsFiles, angFiles) ),
                tasks: ['jshint', 'ngAnnotate', 'concat', 'uglify'],
                options: {
                    spawn: false
                }
            }
        }
    });

    // Load required grunt/node modules
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-ng-annotate');
    grunt.loadNpmTasks('grunt-contrib-watch');
    // Task definitions
    grunt.registerTask('default', ['jshint', 'ngAnnotate', 'concat', 'uglify'] );
    
};
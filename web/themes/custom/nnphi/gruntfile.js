module.exports = function(grunt) {
    // Configuration goes here
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

    // Sass configuration
    sass: {
        dist: {
            options: {
                style: 'compressed'
            },
            files: {
                'style.css': 'sass/style.scss'
            }
        } 
    },

    // Autoprefixer to rewrite file with vendor prefixes
    autoprefixer: {
       // prefix the specified file
       single_file: {
         options: {
           // Target-specific options go here.
         },
         src: 'style.css',
         dest: 'style.css'
       }
     },

      // Watcher with live reload. With this on, you can use live reload in your browser to see live CSS changes.
      // See theme readme for details
      watch: {
      css: {
          files: ['sass/*.scss', 'sass/*/*.scss', 'sass/*/*/*.scss', 'sass/*/*/*/*.scss'],
          tasks: ['autoprefixer', 'sass'],
          options: {
            spawn: false,
            livereload: true
          }
        },
      js: {
        files: ['js/src/*.js'],
        tasks: ['concat', 'babel']
        }
      },

      concat: {
        options: {
          // Custom function to remove all export and import statements
          process: function (src) {
            return src.replace(/^(export|import).*/gm, '')
          }
        },
        dist: {
          src: [
            // Add script paths here in the order you want them compiled
            // You have to manually request bootstrap scripts you want to use
            'js/src/base.js',
            'js/src/header.js',
            'js/src/scripts.js',
            'js/src/bootstrap/dropdown.js',
            'js/src/bootstrap/util.js'
          ],
          dest: 'dist/scripts.js'

        }
      },

      babel: {
        dist: {
          options: {
            "presets": [
              [
                "es2015",
                {
                  "modules": false,
                  "loose": true
                }
              ]
            ],
            "plugins": [
              "transform-es2015-modules-strip"
            ]
          },


          files: {
            'scripts.js': 'dist/scripts.js'
          }
        }
      },

      // minify js
      uglify: {
        options: {
          mangle: false
        },
        my_target: {
          files: {
            'scripts.js': ['scripts.js']
          }
        }
      }

    });


   // Where we tell Grunt we plan to use this plug-in.
   grunt.loadNpmTasks('grunt-contrib-sass');
   grunt.loadNpmTasks('grunt-autoprefixer');
   grunt.loadNpmTasks('grunt-contrib-watch');
   grunt.loadNpmTasks('grunt-contrib-concat');
   grunt.loadNpmTasks('grunt-babel');
   grunt.loadNpmTasks('grunt-contrib-uglify');

   // Where we tell Grunt what to do when we type "grunt" into the terminal. By default this sets up watch.
   grunt.registerTask('default', ['concat', 'babel', 'sass', 'autoprefixer', 'uglify', 'watch']);

   // Sets up task so that typing "grunt compile" will compile CSS without watching
   grunt.registerTask('compile', ['sass', 'autoprefixer']);

};

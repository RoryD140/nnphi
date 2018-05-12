module.exports = function(grunt) {


    // Since we're compiling a module JS file with Babel, we need to set file paths relative to the gruntfile
    const path = require('path');

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
                path.resolve() + "/node_modules/babel-preset-es2015",
                {
                  "modules": false,
                  "loose": true
                }
              ]
            ],
            "plugins": [
              path.resolve() + "/node_modules/babel-plugin-transform-es2015-modules-strip",
            ]
          },


          files: {
            'scripts.js': 'dist/scripts.js',
            '../../../modules/custom/nnphi_training/js/search.min.js': '../../../modules/custom/nnphi_training/js/search.js', // Compile training search js
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

   // Just run JS Babel
   grunt.registerTask('js', ['babel']);

};

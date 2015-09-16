var gulp    = require('gulp')
  , sass    = require('gulp-sass')
  , concat  = require("gulp-concat")
  , jshint  = require("gulp-jshint")
  , stylish = require('jshint-stylish')
  , uglify  = require("gulp-uglify");

gulp.task('sass', function () {
  gulp.src('./app/css/sass/*.scss')
  .pipe(sass({imagePath: '../img', outputStyle:'compressed',includePaths: require('node-neat').includePaths}))
  .pipe(gulp.dest('./app/public/css/'));
});

gulp.task('js_components', function () {
  gulp.src([
      './app/bower_components/angular/angular.js'
    , './app/bower_components/angular-ui-router/release/angular-ui-router.js'
    , './app/bower_components/angular-resource/angular-resource.js'
    , './app/bower_components/angular-sanitize/angular-sanitize.js'
    , './app/bower_components/ngInfiniteScroll/build/ng-infinite-scroll.js'
  ]) // path to your files
  .pipe(concat('lib.js'))  // concat and name it "concat.js"
  .pipe(uglify({mangle:false}))
  .pipe(gulp.dest('./app/public/js'));
});

gulp.task('js', function () {
  gulp.src(
    [
      '!./app/bower_components/**/*.js',
      '!./app/js/lib/polyfills/**/*.js',
      '!./app/js/**/*.min.js',
      './app/js/lib/alchemy*.js',
      './app/js/lib/jquery.alchemy*.js',
      './app/js/lib/jquery.move*.js',
      './app/js/lib/html2canvas.js',
      './app/js/lib/search_tools.js',
      './app/js/lib/bxslider/jquery.bxslider.js',
      './app/js/lib/foundation*/foundation/*.js',
      // './app/js/lib/zone.js',
      // './app/js/lib/designer.js',
      // './app/js/lib/designer_workspace.js',
      './app/js/app.js',
      './app/js/controllers/**/*.js',
      './app/js/services/**/*.js',
      './app/js/filters/**/*.js',
      './app/js/directives/**/*.js'
    ]
  ) // path to your files
  .pipe(jshint())
  .pipe(jshint.reporter(stylish))
  .pipe(concat('jewelrydesigner_app.js'))  // concat and name it "concat.js"
  .pipe(uglify({mangle:false}))
  .pipe(gulp.dest('./app/public/js'));
});

// gulp.task('js_polyfills', function () {
//   gulp.src(
//     [
//       '!./app/bower_components/**/*.js',
//       './app/js/lib/polyfills/**/*.js'
//     ]
//   ) // path to your files
//   .pipe(jshint())
//   .pipe(jshint.reporter(stylish))
//   .pipe(concat('polyfills.js'))  // concat and name it "concat.js"
//   .pipe(uglify({mangle:false}))
//   .pipe(gulp.dest('./app/public/js/polyfills'));
// });


// Watch Files For Changes
gulp.task('watch', function() {
  // CSS
  gulp.watch('./app/css/sass/**/*.scss', ['sass']);

  // JS
  gulp.watch(['./app/js/**/*.js'], ['js']);
});

// The default task (called when you run `gulp` from cli)
gulp.task('default', ['sass', 'js_components', 'js', 'watch']);

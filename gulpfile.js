const gulp         = require('gulp');
const sass         = require('gulp-sass');
const postcss      = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssnano      = require('cssnano');
const gulpWebpack  = require('webpack-stream');
const uglify       = require("gulp-uglify");
const sourcemaps   = require('gulp-sourcemaps');
const rename       = require('gulp-rename');
const path         = require('path');
/**
 * Webpack configuration object
 */
let wp_options = {
	entry: {
		'public': './src/scripts/public.js',
		'admin': './src/scripts/admin.js',
	},
	output: {
		filename: '[name].js',
		// path: path.resolve( __dirname, 'assets/lasjd' )
	},
	mode: "production",
	externals: {
	  'jquery': 'jQuery'
	},
	module : {
		rules : [
			{
				test: /.js$/,
				exclude: /(node_modules)/,
				use : {
					loader : 'babel-loader',
					options: {
						presets: [ "@wordpress/babel-preset-default" ]
					}
				}
			}
		]
	}
}
/**
 * Compile scss
 */
gulp.task( 'sass', function(){
	return gulp.src( 'src/styles/**/*.scss' )
	.pipe( sourcemaps.init() )
	.pipe( sass().on( 'error', sass.logError ) )
	.pipe( sourcemaps.write() )
	.pipe( gulp.dest( 'assets/css' ) )
} );
/**
 * Css Tasks
 */
gulp.task( 'css', gulp.series( 'sass', function () {
	return gulp.src( [ './assets/css/public.css', './assets/css/admin.css' ] )
	.pipe( postcss( [
		autoprefixer( { grid : 'autoplace' } ),
		cssnano()
	] ) )
	.pipe( rename( { suffix: '.min' } ) )
	.pipe( gulp.dest( './assets/css' ) );
} ) );
/**
 * Task to compile JS using webpack with enironment set to production
 */
gulp.task( 'webpack:dist', function() {
	/**
	 * Set the production environment
	 */
	wp_options.mode = 'production';
	/**
	 * Start the task
	 */
	return gulp.src( 'src/scripts/*.js' )
	.pipe( gulpWebpack( { config : wp_options }, require( 'webpack' ) ) )
	.on('error', function handleError() {
		this.emit('end'); // Recover from errors
	})
	.pipe( gulp.dest( 'assets/js' ) );
} );
/**
 * Task to build the javascript bundle using production settings
 */
gulp.task( 'js:dist', gulp.series( [ 'webpack:dist' ], function(){
	return gulp.src( [ 'assets/js/public.js', 'assets/js/admin.js' ] )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( uglify() )
		.pipe( gulp.dest('assets/js') );
} ) );
/**
 * Task to compile JS using webpack with enironment set to production
 */
gulp.task( 'webpack:dev', function() {
	/**
	 * Set the production environment
	 */
	wp_options.mode = 'development';
	/**
	 * Start the task
	 */
	return gulp.src( 'src/scripts/*.js' )
	.pipe( gulpWebpack( { config : wp_options }, require( 'webpack' ) ) )
	.on('error', function handleError() {
		this.emit('end'); // Recover from errors
	})
	.pipe( gulp.dest( 'assets/js' ) );
} );
/**
 * Task to build the javascript bundle using dev settings
 */
gulp.task( 'js:dev', gulp.series( [ 'webpack:dev' ], function(){
	return gulp.src( [ 'assets/js/public.js', 'assets/js/admin.js' ] )
		.pipe( sourcemaps.init() )
		.pipe( sourcemaps.write('./') )
		.pipe( gulp.dest('assets/js') );
} ) );

gulp.task( 'watch', function(){
	gulp.watch( 'src/styles/**/*.scss', gulp.series( 'css' ) );
	gulp.watch( 'src/scripts/**/*.js', gulp.series( [ 'js:dist', 'js:dev' ] ) );
});

gulp.task('default', gulp.series( [ 'watch' ] ));

/**
 * Set the build path
 */
gulp.task( 'build', gulp.series( [ 'css', 'js:dist', 'js:dev' ] ) );
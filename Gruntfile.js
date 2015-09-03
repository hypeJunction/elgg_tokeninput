module.exports = function (grunt) {

	var package = grunt.file.readJSON('package.json');

	// Project configuration.
	grunt.initConfig({
		pkg: package,
		// Bump version numbers
		version: {
			pkg: {
				src: ['package.json', 'composer.json'],
			},
			manifest: {
				options: {
					pkg: grunt.file.readJSON('package.json'),
					prefix: '\<version type=\"dist\"\>'
				},
				src: ['manifest.xml'],
			}
		},
		clean: {
			release: {
				src: ['build/', 'releases/', 'vendor/', 'composer.lock']
			}
		},
		copy: {
			release: {
				src: [
					'**',
					'!**/.git*',
					'!releases/**',
					'!build/**',
					'!node_modules/**',
					'!package.json',
					'!config.rb',
					'!sass/**',
					'!tests/**',
					'!composer.json',
					'!composer.lock',
					'!package.json',
					'!phpunit.xml',
					'!Gruntfile.js',
					'!Gemfile',
					'!Gemfile.lock'
				],
				dest: 'build/',
				expand: true
			},
		},
		compress: {
			release: {
				options: {
					archive: 'releases/<%= pkg.name %>-<%= pkg.version %>.zip'
				},
				cwd: 'build/',
				src: ['**/*'],
				dest: '<%= pkg.name %>/',
				expand: true
			}
		},
		gitcommit: {
			release: {
				options: {
					message: 'Release <%= pkg.version %>',
				},
				files: {
					src: ["composer.json", "manifest.xml", "package.json"],
				}
			},
		},
		gitpush: {
			release: {
				
			},
		},
		gh_release: {
			options: {
				token: process.env.GITHUB_TOKEN,
				repo: package.repository.repo,
				owner: package.repository.owner
			},
			release: {
				tag_name: '<%= pkg.version %>',
				target_commitish: 'dev',
				name: 'Release <%= pkg.version %>',
				body: 'Self-contained ZIP distribution for <%= pkg.name %>',
				draft: false,
				prerelease: false,
				asset: {
					name: '<%= pkg.name %>-<%= pkg.version %>.zip',
					file: 'releases/<%= pkg.name %>-<%= pkg.version %>.zip',
					'Content-Type': 'application/zip'
				}
			}
		}
	});

	// Load all grunt plugins here
	grunt.loadNpmTasks('grunt-version');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-composer');
	grunt.loadNpmTasks('grunt-git');
	grunt.loadNpmTasks('grunt-gh-release');

	grunt.registerTask('readpkg', 'Read in the package.json file', function () {
		grunt.config.set('pkg', grunt.file.readJSON('package.json'));
	});

	// Release task
	grunt.registerTask('release', function (n) {
		var n = n || 'patch';
		grunt.task.run([
			'version::' + n,
			'readpkg',
			'gitcommit:release',
			'gitpush:release',
			'clean:release',
			'composer:install:no-dev:prefer-dist',
			'copy:release',
			'compress:release',
			'gh_release',
			'clean:release',
		]);
	});
};
styles:
	sass --scss -t compressed --sourcemap=auto --unix-newlines src/AppBundle/Resources/sass/kubithon.scss > src/AppBundle/Resources/public/css/kubithon.min.css
	sass --scss -t expanded --sourcemap=auto --unix-newlines src/AppBundle/Resources/sass/kubithon.scss > src/AppBundle/Resources/public/css/kubithon.css

watch:
	sass --scss -t compressed --watch --trace src/AppBundle/Resources/sass/kubithon.scss:src/AppBundle/Resources/public/css/kubithon.min.css

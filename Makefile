default:
	git rm -r --cached modules
	git rm .gitmodules
	rm -rf modules/*/.git \
		   Makefile \
		   .travis.yml

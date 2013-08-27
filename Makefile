VENDOR_BIN=vendor/bin
SRC_DIR=src
DOC_DIR=doc

PHPUNIT=${VENDOR_BIN}/phpunit
PHPMD=${VENDOR_BIN}/phpmd
PHPCS=${VENDOR_BIN}/phpcs
APIGEN=${VENDOR_BIN}/apigen.php

test:
	@${PHPUNIT}

md:
	@${PHPMD} ${SRC_DIR} \
		text \
		cleancode,codesize,controversial,design,naming,unusedcode

cs:
	@${PHPCS} ${SRC_DIR}

quality: test md cs

doc:
	@${APIGEN} --source ${SRC_DIR} \
		--destination ${DOC_DIR} \
		--php no

.PHONY: doc test quality

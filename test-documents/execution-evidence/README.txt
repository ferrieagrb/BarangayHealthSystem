Execution scope: local automated tests only; all application source unchanged.
Existing suite:
C:\xampp\php\php.exe vendor\phpunit\phpunit\phpunit --configuration <temporary phpunit.execution.xml> --do-not-cache-result --log-junit phpunit-junit.xml --testdox-text phpunit-testdox.txt
Focused acceptance:
C:\xampp\php\php.exe vendor\phpunit\phpunit\phpunit --configuration <temporary phpunit.execution.xml> --filter test_execution_ --do-not-cache-result --log-junit acceptance-junit.xml <temporary acceptance directory>
Stock diagnostic rerun:
C:\xampp\php\php.exe vendor\phpunit\phpunit\phpunit --configuration <temporary phpunit.execution.xml> --filter test_execution_STK_007 --do-not-cache-result --log-junit stock-recheck-junit.xml <temporary acceptance directory>
The focused harness and config exist in the temporary bheams-execution-20261119 folder.
The report counts 259 unique checks; the single diagnostic rerun is not counted twice.
No code fixes were made. No browser or live-site result is claimed.
Harness source is also included as .php.txt files for review. The stock harness includes the additional diagnostic assertion used in the separately recorded rerun.

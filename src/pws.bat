@echo off
phing -f @PEAR-DIR@/PWS/build.xml -Dcurrentdir=%CD% -Dprojectname=%1 generate

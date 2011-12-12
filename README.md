PWS(PHP Web Services) - a framework to build soap web services using document-first approach.

# Instalation
Currently installation from pear channel unavailable. So you can only build and install it locally.
First you need to install phing - php build system like ant for java.
  
      sudo pear channel-discover pear.phing.info 
      sudo pear install phing/phing

Now you ready to install pws.

      git clone git://github.com/kasyaar/pws.git 
      cd pws
      phing

On fresh instalation of pear you maybe need to change stability from stable to beta.

      sudo pear config-set preferred_state beta 

# Usage

Setup your first project and test it(russian only):

http://redmine.kasimtsev.com/projects/pws/news#%D0%A3%D1%81%D1%82%D0%B0%D0%BD%D0%BE%D0%B2%D0%BA%D0%B0

http://redmine.kasimtsev.com/news/3#Create-new-project


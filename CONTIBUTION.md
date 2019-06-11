#Contribution Guide

##code sharing way in PHPStorm


1. Check working directory. `ALT+ 9`
2. commit changes. `CTRL+K` and write message and `CTRL+ENTER`
3. push code if you are doing alone on this branch. otherwise pull (fetch + merge). `CTRL+SHIFT+K` to open dialog and `CTRL+ENTER` to push code. 


##Deployment Flow

### Live Server . 
on clean wokring directory. apply below command will apply all your patches or latest code.
    git pull origin master

### Staging 
test on live master branch. 


##Development Flow


### Hotfixing 
* checkout master branch 
* code and push to repo( repo is not connected to live)
* pull on staging and tested it 
* pull on live and tested it 
* if failed on live 
     * git checkout prevous-commit-id
     * changes code (hotfixing on master branch, test on staging)
     * git checkout master ( live)
     * git pull (new changes hotfixed and test it)
 
### Task based
* checkout new barnach with name task from staging
* Individual on branch
     * code
     * commit
     * push  
* Multiple developer on same branch 
  * code 
  * commit 
  * pull ( fetch + merge)
  * resolve conflict, merge and commit
  * push
 
### Branches Structure 
* Master 
 * Staging 
    * Dev ( features based or modulator based or based on the tasks)
    * task1
    * etc ( all task barnaches )
    

### merging flow
 * always merge your working, coding or task branch to `staging` branch. 
 * only staging branch will merge to master after tested 
 * **merge master into staging (if there is any hotfixing first before) merging staging into master**
 * **use master only for hotfixing**


## Merging dev branch with stable branch 

###Suppose
* development branch name is `dev`
* stable branch name is `master`
* remote name is `origin`

Follow these steps to merge with stable. 

### clean current branch
* comment dev branch and clean working directory.
* pull( `fetch` + `merge`(**remote/dev** with **local/dev** branch))
* push your `dev` branch to live

### merge stable remote branch with local branch

* fetch all branches(press `CTRL+SHIFT+A`, write fetch and `ENTER`)
* checkout your stable branch `master` (press `CTRL+SHIFT+A`, write branches and `ENTER`, select your branch and `checkout`)
* `merge` (`remote/origin/master` in `local/master`) (press `CTRL+SHIFT+A`, write branches and `ENTER`, select your `origin/master`, `merge into current` and `ENTER`)

Note: Now, your stable branch is upto date with remote. Now you can merge your stable with unstable branch. 
### Merge stable with unstable branch 

* `checkout` to unstable branch `dev`
* `merge` stable branch `master` with unstable branch `dev`  (press `CTRL+SHIFT+A`, write branches and `ENTER`, select your `local/master`, `merge into current` and `ENTER`)

* resolve conflict, `commit` and `push`
* test local branch, resolve issue, `commit` and `push`

### merge unstable to stable branch

* `checkout` to stable branch `master`
* `merge` unstable branch `dev` with stable branch `master`  (press `CTRL+SHIFT+A`, write branches and `ENTER`, select your `local/dev`, `merge into current` and `ENTER`)


#Developers

1. Engr Bilal Sheikh
2. Mustafa Mughal
3. Shehbaz Ali(shehbaz@redsignal.biz)

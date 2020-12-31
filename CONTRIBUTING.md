# Contributing

## Resources

Welcome! If you wish to contribute to this project, be sure to read the following resources:

- [Developer Hub](https://developers.fioprotocol.io/)
- [KnowledgeBase](https://kb.fioprotocol.io/)
- [Chat on Discord](https://discord.gg/pHBmJCc)
- [Coding Standards](https://github.com/link-is-tbd)
- [Code of Conduct (Is one needed?)](is-there-one)

If you are working on new features or refactoring, [create a proposal](./put-proper-link-here).

## Running Tests

To run tests:

- Clone the repository.
  Click the "Clone or download" button on the repository to find both the URL and instructions on cloning.

- Install dependencies via composer:

  ```console
  $ composer install
  ```

  If you don't have `composer` installed, download it from https://getcomposer.org/download/

- Run the tests using the "test" command shipped in the `composer.json`:

  ```console
  $ composer test
  ```
You can turn on conditional tests with the `phpunit.xml` file. To do so:

- Copy the `phpunit.xml.dist` file to `phpunit.xml`
- Edit the `phpunit.xml` file to enable any specific functionality you want to test, as well as to provide appropriate test values.

## Running Coding Standards Checks

TBD

## Release Branches

TBD

## Recommended Workflow for Contributions

Under discussion

### Keeping Up-to-Date

Periodically, you should update your fork or personal repository to match the canonical repository.
Assuming you have setup your local repository per the instructions above, you can do the following:

```console
$ git fetch origin
$ git switch {branch to update}
$ git pull --rebase --autostash
# OPTIONALLY, to keep your remote up-to-date -
$ git push fork {branch}:{branch}
```

If you're tracking other release branches, you'll want to do the same operations for each branch.

### Working on a patch

We recommend you do each new feature or bugfix in a new branch.
This simplifies the task of code review as well as the task of merging your changes into the canonical repository.

A typical workflow will then consist of the following:

1. Create a new local branch based off the appropriate release branch.
2. Switch to your new local branch.
   (This step can be combined with the previous step with the use of `git switch -c {new branch} {original branch}`, or, if the original branch is the current one, `git switch -c {new branch}`.)
3. Do some work, commit, repeat as necessary.
4. Push the local branch to your remote repository.
5. Send a pull request.

The mechanics of this process are actually quite trivial. Below, we will
create a branch for fixing an issue in the tracker.

```console
$ git switch -c hotfix/9295
Switched to a new branch 'hotfix/9295'
```

... do some work ...


```console
$ git commit
```
... write your log message ...

```console
$ git push fork hotfix/9295:hotfix/9295
Counting objects: 38, done.
Delta compression using up to 2 threads.
Compression objects: 100% (18/18), done.
Writing objects: 100% (20/20), 8.19KiB, done.
Total 20 (delta 12), reused 0 (delta 0)
To ssh://git@github.com/{username}/laminas-zendframework-bridge.git
   b5583aa..4f51698  HEAD -> hotfix/9295
```

To send a pull request, you have several options.

If using GitHub, you can do the pull request from there.
Navigate to your repository, select the branch you just created, and then select the "Pull Request" button in the upper right.
Select the user/organization "laminas" (or whatever the upstream organization is) as the recipient.

You can also perform the same steps via the [GitHub CLI tool](https://cli.github.com).
Execute `gh pr create`, and step through the dialog to create the pull request.
If the branch you will submit against is not the default branch, use the `-B {branch}` option to specify the branch to create the patch against.

#### Which branch to issue the pull request against?

- For fixes against the stable release...
- For new features, or fixes that introduce new elements to the public API...

### Branch Cleanup

As you might imagine, if you are a frequent contributor, you'll start to get a ton of branches both locally and on your remote.

Once you know that your changes have been accepted to the canonical repository, we suggest doing some cleanup of these branches.

- Local branch cleanup

  ```console
  $ git branch -d <branchname>
  ```

- Remote branch removal

  ```console
  $ git push fork :<branchname>
  ```

## Contributing Documentation

The documentation is available in the "**docs/**" directory, is written in [Markdown format], and is built using ...
To learn more about how to contribute to the documentation for a repository, including how to setup the documentation locally, please refer to TBD.

[MkDocs]: https://www.mkdocs.org/
[Markdown format]: https://www.markdownguide.org/
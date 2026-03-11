# Release guide

## Update version

Edit:

- `setup.php`
- `CHANGELOG.md`

## Commit and tag

```bash
git add .
git commit -m "Release v0.1.0"
git tag v0.1.0
git push origin main --tags
```

## GitHub Release

- Wait for the workflow to finish
- Open the new tag in **Releases**
- Publish the release
- Verify both assets:
- `tacticalrmmremote.tar.gz`
- `tacticalrmmremote.zip`

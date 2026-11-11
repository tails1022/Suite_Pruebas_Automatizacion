import pytest
import os

@pytest.hookimpl(tryfirst=True)
def pytest_configure(config):
    # Crear directorios necesarios
    os.makedirs("evidences", exist_ok=True)
    os.makedirs("reports", exist_ok=True)

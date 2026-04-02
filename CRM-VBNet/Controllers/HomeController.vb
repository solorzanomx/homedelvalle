' =============================================
' HomeController.vb
' Dashboard principal - primera vista después del login
' =============================================
Imports System.Web.Mvc
Imports CRM_VBNet.Models
Imports CRM_VBNet.Helpers

Namespace CRM_VBNet.Controllers

    Public Class HomeController
        Inherits Controller

        Private ReadOnly _db As DatabaseContext

        Public Sub New()
            _db = New DatabaseContext(System.Configuration.ConfigurationManager.ConnectionStrings("CRMConnection").ConnectionString)
        End Sub

        ' GET: /Home/Index (Dashboard)
        Function Index() As ActionResult
            If Not SessionHelper.IsAuthenticated() Then
                Return RedirectToAction("Login", "Account")
            End If

            Dim user = SessionHelper.GetCurrentUser()
            ViewBag.User = user
            ViewBag.SiteSettings = _db.GetSiteSettings()

            ' Datos para el dashboard
            Dim allUsers = _db.GetAllUsers()
            ViewBag.TotalUsers = allUsers.Count
            ViewBag.ActiveUsers = allUsers.Where(Function(u) u.IsActive).Count()
            ViewBag.RecentUsers = allUsers.Take(5).ToList()

            Return View()
        End Function

    End Class

End Namespace
